<?php
// src/Controller/Api/V1/Review/ReviewController.php

namespace App\Controller\Api\V1\Review;

use App\Domain\Admin\Service\AuditService;
use App\Domain\Review\DTO\CreateReviewRequestDTO;
use App\Domain\Review\Repository\ReviewRepository;
use App\Domain\Review\Service\ReviewService;
use App\Domain\User\Entity\User;
use App\Security\Voter\ReviewVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1', name: 'api_v1_review_')]
final class ReviewController extends AbstractController
{
    public function __construct(
        private readonly ReviewService         $reviewService,
        private readonly ReviewRepository      $reviewRepository,
        private readonly AuditService          $auditService,
        private readonly EntityManagerInterface $em,
    ) {}

    /**
     * POST /api/v1/bookings/{bookingId}/review
     */
    #[Route('/bookings/{bookingId}/review', name: 'create', methods: ['POST'], requirements: ['bookingId' => '\d+'])]
    public function create(
        int $bookingId,
        #[MapRequestPayload] CreateReviewRequestDTO $dto,
        #[CurrentUser] User $user,
    ): JsonResponse {
        $review = $this->reviewService->create($bookingId, $dto, $user);

        return $this->json(['data' => $review], Response::HTTP_CREATED);
    }

    /**
     * GET /api/v1/masters/{masterId}/reviews?page=1&perPage=10
     * Публичный endpoint — без авторизации
     */
    #[Route('/masters/{masterId}/reviews', name: 'master_list', methods: ['GET'], requirements: ['masterId' => '\d+'])]
    public function masterReviews(
        int $masterId,
        #[MapQueryParameter] int $page    = 1,
        #[MapQueryParameter] int $perPage = 10,
    ): JsonResponse {
        $result = $this->reviewService->getMasterReviews($masterId, $page, $perPage);

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
     * PATCH /api/v1/reviews/{id}/hide
     */
    #[Route('/reviews/{id}/hide', name: 'hide', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function hide(int $id, #[CurrentUser] User $admin): JsonResponse
    {
        $review = $this->reviewRepository->find($id);

        if ($review === null) {
            return $this->json(['message' => 'Отзыв не найден'], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted(ReviewVoter::MODERATE, $review);

        $review->hide();
        $this->auditService->log($admin, $review, 'hide_review');

        $this->em->flush();

        return $this->json(['data' => ['id' => $review->getId(), 'isVisible' => false]]);
    }

    /**
     * PATCH /api/v1/reviews/{id}/show
     */
    #[Route('/reviews/{id}/show', name: 'show', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function show(int $id, #[CurrentUser] User $admin): JsonResponse
    {
        $review = $this->reviewRepository->find($id);

        if ($review === null) {
            return $this->json(['message' => 'Отзыв не найден'], Response::HTTP_NOT_FOUND);
        }

        $review->show();
        $this->auditService->log($admin, $review, 'show_review');

        $this->em->flush();

        return $this->json(['data' => ['id' => $review->getId(), 'isVisible' => true]]);
    }
}
