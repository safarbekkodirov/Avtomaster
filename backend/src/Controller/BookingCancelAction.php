<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractController;
use App\Controller\Base\Constants\BookingStatus;
use App\Entity\Booking;
use App\Entity\NotificationBuilder;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BookingCancelAction extends AbstractController
{
    public function __invoke(
        Request $request,
        Booking $data,
        BookingRepository $bookingRepository,
        EntityManagerInterface $em,
    ): Booking {
        $booking = $bookingRepository->find($data->getId());
        if (!$booking) {
            throw new NotFoundHttpException('Booking not found');
        }

        $currentStatus = $booking->getStatus();
        if ($currentStatus !== BookingStatus::PENDING && $currentStatus !== BookingStatus::CONFIRMED) {
            throw new BadRequestHttpException('Only pending or confirmed bookings can be cancelled');
        }

        $body = json_decode($request->getContent(), true);
        $reason = $body['reason'] ?? null;

        $booking->setStatus(BookingStatus::CANCELLED);
        $booking->setCancelledReason($reason);
        $booking->setUpdatedAt(new DateTime());

        $client = $booking->getClient();
        if ($client) {
            $notification = (new NotificationBuilder())
                ->bookingCancelled($booking)
                ->build($client);
            $em->persist($notification);
        }

        $em->flush();

        return $booking;
    }
}
