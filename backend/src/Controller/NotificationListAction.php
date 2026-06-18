<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractController;
use App\Repository\NotificationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/notifications', methods: ['GET'])]
class NotificationListAction extends AbstractController
{
    public function __invoke(
        NotificationRepository $notificationRepository,
        Request $request,
    ): Response {
        $page    = max(1, (int) $request->query->get('page', 1));
        $perPage = max(1, min(50, (int) $request->query->get('perPage', 20)));
        $offset  = ($page - 1) * $perPage;

        $notifications = $notificationRepository->findByUser($this->getUser(), $perPage, $offset);
        $total         = $notificationRepository->countByUser($this->getUser());
        $totalPages    = (int) ceil($total / $perPage);

        $serialized = $this->getSerializer()->serialize([
            'data' => $notifications,
            'pagination' => [
                'page'       => $page,
                'perPage'    => $perPage,
                'total'      => $total,
                'totalPages' => $totalPages,
            ],
        ], 'json', [
            'groups' => ['notification:read'],
        ]);

        return new Response($serialized, Response::HTTP_OK, [
            'Content-Type' => 'application/json',
        ]);
    }
}
