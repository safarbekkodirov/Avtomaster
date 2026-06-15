<?php
// src/Controller/Api/V1/Booking/BookingController.php

namespace App\Controller\Api\V1\Booking;

use App\Domain\Booking\DTO\CancelBookingRequestDTO;
use App\Domain\Booking\DTO\CreateBookingRequestDTO;
use App\Domain\Booking\Repository\BookingRepository;
use App\Domain\Booking\Service\BookingService;
use App\Security\Voter\BookingVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Domain\User\Entity\User;

#[Route('/api/v1/bookings', name: 'api_v1_booking_')]
final class BookingController extends AbstractController
{
    public function __construct(
        private readonly BookingService    $bookingService,
        private readonly BookingRepository $bookingRepository,
    ) {}

    /**
     * POST /api/v1/bookings
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] CreateBookingRequestDTO $dto,
        #[CurrentUser] User $user,
    ): JsonResponse {
        $booking = $this->bookingService->create($dto, $user);

        return $this->json(['data' => $booking], Response::HTTP_CREATED);
    }

    /**
     * GET /api/v1/bookings?page=1&perPage=20
     * Клиент видит свои, мастер — свои
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(
        #[CurrentUser] User $user,
        #[MapQueryParameter] int $page    = 1,
        #[MapQueryParameter] int $perPage = 20,
        #[MapQueryParameter] ?string $status = null,
    ): JsonResponse {
        $result = $user->hasRole('ROLE_MASTER')
            ? $this->bookingService->getMasterBookings($user, $page, $perPage, $status)
            : $this->bookingService->getClientBookings($user, $page, $perPage);

        return $this->json([
            'data'       => $result['items'],
            'pagination' => [
                'page'       => $result['page'],
                'perPage'    => $result['perPage'],
                'total'      => $result['total'],
                'totalPages' => (int) ceil($result['total'] / $result['perPage']),
            ],
        ]);
    }

    /**
     * GET /api/v1/bookings/{id}
     */
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, #[CurrentUser] User $user): JsonResponse
    {
        $booking = $this->bookingRepository->find($id);

        if ($booking === null) {
            return $this->json(['message' => 'Бронирование не найдено'], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted(BookingVoter::VIEW, $booking);

        return $this->json(['data' => \App\Domain\Booking\DTO\BookingResponseDTO::fromEntity($booking)]);
    }

    /**
     * PATCH /api/v1/bookings/{id}/confirm
     */
    #[Route('/{id}/confirm', name: 'confirm', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function confirm(int $id, #[CurrentUser] User $user): JsonResponse
    {
        $booking = $this->bookingService->confirm($id, $user);

        return $this->json(['data' => $booking]);
    }

    /**
     * PATCH /api/v1/bookings/{id}/complete
     */
    #[Route('/{id}/complete', name: 'complete', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function complete(int $id, #[CurrentUser] User $user): JsonResponse
    {
        $booking = $this->bookingService->complete($id, $user);

        return $this->json(['data' => $booking]);
    }

    /**
     * PATCH /api/v1/bookings/{id}/cancel
     */
    #[Route('/{id}/cancel', name: 'cancel', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function cancel(
        int $id,
        #[MapRequestPayload] CancelBookingRequestDTO $dto,
        #[CurrentUser] User $user,
    ): JsonResponse {
        $booking = $this->bookingService->cancel($id, $dto, $user);

        return $this->json(['data' => $booking]);
    }
}
