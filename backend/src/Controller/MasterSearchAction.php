<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractController;
use App\Repository\MasterRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MasterSearchAction extends AbstractController
{
    public function __invoke(
        Request $request,
        MasterRepository $masterRepository,
        NormalizerInterface $normalizer,
    ): JsonResponse {
        $regionName   = $request->query->get('regionName');
        $categorySlug = $request->query->get('categorySlug');
        $minRating    = $request->query->get('minRating');
        $sortBy       = $request->query->get('sortBy', 'rating');
        $page         = max(1, (int) $request->query->get('page', 1));
        $perPage      = max(1, min(100, (int) $request->query->get('perPage', 20)));
        $userLat      = $request->query->get('lat') ? (float) $request->query->get('lat') : null;
        $userLng      = $request->query->get('lng') ? (float) $request->query->get('lng') : null;

        $result = $masterRepository->search(
            regionName:   $regionName,
            categorySlug: $categorySlug,
            minRating:    $minRating ? (float) $minRating : null,
            sortBy:       $sortBy,
            page:         $page,
            perPage:      $perPage,
        );

        $normalized = $normalizer->normalize(
            $result['data'],
            null,
            ['groups' => ['masters:read', 'master:read']]
        );

        if ($userLat !== null && $userLng !== null) {
            foreach ($normalized as &$master) {
                $masterLat = isset($master['lat']) ? (float) $master['lat'] : null;
                $masterLng = isset($master['lng']) ? (float) $master['lng'] : null;
                $master['distanceKm'] = ($masterLat !== null && $masterLng !== null)
                    ? round($this->haversine($userLat, $userLng, $masterLat, $masterLng), 1)
                    : null;
            }
            unset($master);

            if ($sortBy === 'distance') {
                usort($normalized, fn($a, $b) => ($a['distanceKm'] ?? 9999) <=> ($b['distanceKm'] ?? 9999));
            }
        }

        return new JsonResponse([
            'data'       => $normalized,
            'pagination' => $result['pagination'],
        ]);
    }

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
