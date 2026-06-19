<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractController;
use App\Entity\MasterService;
use App\Repository\MasterRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MasterServicesListAction extends AbstractController
{
    public function __invoke(
        int $id,
        MasterRepository $masterRepository,
        NormalizerInterface $normalizer,
    ): JsonResponse {
        $master = $masterRepository->find($id);
        if (!$master) {
            return new JsonResponse(['error' => 'Master not found'], 404);
        }

        $services = $master->getServices()->toArray();

        $normalized = $normalizer->normalize(
            $services,
            null,
            ['groups' => ['master:read', 'master_service:read', 'service_category:read']]
        );

        return new JsonResponse(['data' => $normalized]);
    }
}
