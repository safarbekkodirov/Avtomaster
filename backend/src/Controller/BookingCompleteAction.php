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
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BookingCompleteAction extends AbstractController
{
    public function __invoke(
        Booking $data,
        BookingRepository $bookingRepository,
        EntityManagerInterface $em,
    ): Booking {
        $booking = $bookingRepository->find($data->getId());
        if (!$booking) {
            throw new NotFoundHttpException('Booking not found');
        }

        if ($booking->getStatus() !== BookingStatus::CONFIRMED) {
            throw new BadRequestHttpException('Only confirmed bookings can be completed');
        }

        $booking->setStatus(BookingStatus::COMPLETED);
        $booking->setUpdatedAt(new DateTime());

        $client = $booking->getClient();
        if ($client) {
            $notification = (new NotificationBuilder())
                ->bookingCompleted($booking)
                ->build($client);
            $em->persist($notification);
        }

        $em->flush();

        return $booking;
    }
}
