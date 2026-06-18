<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractController;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/notifications/read-all', methods: ['POST'])]
class NotificationMarkAllReadAction extends AbstractController
{
    public function __invoke(
        NotificationRepository $notificationRepository,
        EntityManagerInterface $em,
    ): Response {
        $notificationRepository->markAllAsRead($this->getUser());
        $em->flush();

        return new Response('{}', Response::HTTP_OK, [
            'Content-Type' => 'application/json',
        ]);
    }
}
