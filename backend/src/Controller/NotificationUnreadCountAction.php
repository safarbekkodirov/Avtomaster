<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractController;
use App\Repository\NotificationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/notifications/unread-count', methods: ['GET'])]
class NotificationUnreadCountAction extends AbstractController
{
    public function __invoke(
        NotificationRepository $notificationRepository,
    ): Response {
        $count = $notificationRepository->countUnreadByUser($this->getUser());

        $serialized = $this->getSerializer()->serialize([
            'count' => $count,
        ], 'json');

        return new Response($serialized, Response::HTTP_OK, [
            'Content-Type' => 'application/json',
        ]);
    }
}
