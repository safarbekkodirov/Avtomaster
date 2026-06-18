<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractController;
use App\Controller\Base\Constants\BookingStatus;
use App\Entity\Booking;
use App\Entity\NotificationBuilder;
use App\Repository\MasterRepository;
use App\Repository\MasterServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BookingCreateAction extends AbstractController
{
    public function __invoke(
        Request $request,
        MasterRepository $masterRepository,
        MasterServiceRepository $serviceRepository,
        EntityManagerInterface $em,
    ): Booking {
        $data = json_decode($request->getContent(), true);

        $masterId  = $data['masterId'] ?? null;
        $serviceId = $data['serviceId'] ?? null;
        $slotDate  = $data['slotDate'] ?? null;
        $startTime = $data['slotStartTime'] ?? null;
        $endTime   = $data['slotEndTime'] ?? null;
        $notes     = $data['notes'] ?? null;

        if (!$masterId || !$serviceId || !$slotDate || !$startTime || !$endTime) {
            throw new BadRequestHttpException('masterId, serviceId, slotDate, slotStartTime, slotEndTime are required');
        }

        $master = $masterRepository->find($masterId);
        if (!$master) {
            throw new NotFoundHttpException('Master not found');
        }

        $service = $serviceRepository->find($serviceId);
        if (!$service) {
            throw new NotFoundHttpException('Service not found');
        }

        $booking = new Booking();
        $booking->setClient($this->getUser());
        $booking->setMaster($master);
        $booking->setService($service);
        $booking->setTotal($service->getPrice());
        $booking->setStatus(BookingStatus::PENDING);
        $booking->setSlotDate(new DateTime($slotDate));
        $booking->setSlotStartTime($startTime);
        $booking->setSlotEndTime($endTime);
        $booking->setNotes($notes);
        $booking->setCreatedAt(new DateTime());

        $em->persist($booking);
        $em->flush();

        $masterUser = $master->getUser();
        if ($masterUser) {
            $notification = (new NotificationBuilder())
                ->bookingCreated($booking)
                ->build($masterUser);
            $em->persist($notification);
            $em->flush();
        }

        return $booking;
    }
}
