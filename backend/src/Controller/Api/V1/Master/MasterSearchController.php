<?php
// src/Controller/Api/V1/Master/MasterSearchController.php

namespace App\Controller\Api\V1\Master;

use App\Domain\Master\DTO\MasterSearchRequestDTO;
use App\Domain\Master\DTO\SlotSearchRequestDTO;
use App\Domain\Master\Repository\MasterRepository;
use App\Domain\Master\Service\MasterSearchService;
use App\Domain\Master\Service\SlotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/masters', name: 'api_v1_master_')]
final class MasterSearchController extends AbstractController
{
    public function __construct(
        private readonly MasterSearchService  $searchService,
        private readonly SlotService          $slotService,
        private readonly MasterRepository     $masterRepository,
    ) {}

    /**
     * GET /api/v1/masters/search?regionSlug=moscow&lat=55.75&lng=37.62&radiusKm=5&categoryId=1&sortBy=distance&page=1&perPage=20
     */
    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(
        #[MapQueryString] MasterSearchRequestDTO $dto,
    ): JsonResponse {
        $result = $this->searchService->search($dto);

        return $this->json([
            'data'       => $result->items,
            'pagination' => [
                'page'       => $result->page,
                'perPage'    => $result->perPage,
                'total'      => $result->total,
                'totalPages' => (int) ceil($result->total / $result->perPage),
            ],
        ]);
    }

    /**
     * GET /api/v1/masters/{id}/slots?dateFrom=2025-01-20&dateTo=2025-01-27&serviceId=3
     */
    #[Route('/{id}/slots', name: 'slots', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function slots(
        int $id,
        #[MapQueryString] SlotSearchRequestDTO $dto,
    ): JsonResponse {
        $slots = $this->slotService->getAvailableSlots($id, $dto);

        // Группируем слоты по дате для удобства фронта
        $grouped = [];
        foreach ($slots as $slot) {
            $date              = $slot->getSlotDate()->format('Y-m-d');
            $grouped[$date][] = [
                'id'        => $slot->getId(),
                'startTime' => $slot->getStartTime()->format('H:i'),
                'endTime'   => $slot->getEndTime()->format('H:i'),
            ];
        }

        return $this->json(['data' => $grouped]);
    }

    /**
     * GET /api/v1/masters/{id} — публичный профиль мастера
     */
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $master = $this->masterRepository->findPublicProfile($id);

        if ($master === null) {
            return $this->json(['message' => 'Мастер не найден'], 404);
        }

        $profile = $master->getUser()->getProfile();
        $services = array_map(fn($s) => [
            'id'              => $s->getId(),
            'name'            => $s->getName(),
            'price'           => (float) $s->getPrice(),
            'durationMinutes' => $s->getDuration(),
        ], $master->getServices()->toArray());

        return $this->json([
            'data' => [
                'id'           => $master->getId(),
                'firstName'    => $profile?->getFirstName() ?? '',
                'lastName'     => $profile?->getLastName() ?? '',
                'avatar'       => $profile?->getAvatar(),
                'bio'          => $master->getBio(),
                'regionName'   => $master->getRegion()->getName(),
                'address'      => $master->getAddress(),
                'rating'       => (float) $master->getRating(),
                'reviewsCount' => $master->getReviewsCount(),
                'isVerified'   => $master->isVerified(),
                'services'     => $services,
            ],
        ]);
    }
}
