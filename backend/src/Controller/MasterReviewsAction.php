<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractController;
use App\Entity\Master;
use App\Repository\MasterRepository;
use App\Repository\ReviewRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/masters/{masterId}/reviews', methods: ['GET'])]
class MasterReviewsAction extends AbstractController
{
    public function __invoke(
        int $masterId,
        MasterRepository $masterRepository,
        ReviewRepository $reviewRepository,
        Request $request,
    ): Response {
        $master = $masterRepository->find($masterId);
        if (!$master) {
            throw new NotFoundHttpException('Master not found');
        }

        $page    = max(1, (int) $request->query->get('page', 1));
        $perPage = max(1, min(50, (int) $request->query->get('perPage', 10)));
        $offset  = ($page - 1) * $perPage;

        $reviews     = $reviewRepository->findByMaster($master, $perPage, $offset);
        $total       = $reviewRepository->countByMaster($master);
        $totalPages  = (int) ceil($total / $perPage);

        $serialized = $this->getSerializer()->serialize([
            'data' => $reviews,
            'pagination' => [
                'page'       => $page,
                'perPage'    => $perPage,
                'total'      => $total,
                'totalPages' => $totalPages,
            ],
        ], 'json', [
            'groups' => ['review:list'],
        ]);

        return new Response($serialized, Response::HTTP_OK, [
            'Content-Type' => 'application/json',
        ]);
    }
}
