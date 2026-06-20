<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\Constants\BookingStatus;
use App\Entity\Booking;
use App\Entity\User;
use App\Entity\NotificationBuilder;
use App\Component\User\UserFactory;
use App\Component\User\UserManager;
use App\Repository\MasterRepository;
use App\Repository\MasterServiceRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/bookings/quick', methods: ['POST'])]
class QuickBookingAction extends AbstractController
{
    public function __invoke(
        Request $request,
        MasterRepository $masterRepository,
        MasterServiceRepository $serviceRepository,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        UserFactory $userFactory,
        UserManager $userManager,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $name      = $data['name'] ?? '';
        $phone     = $data['phone'] ?? '';
        $masterId  = $data['masterId'] ?? null;
        $serviceId = $data['serviceId'] ?? null;
        $slotDate  = $data['slotDate'] ?? null;
        $startTime = $data['slotStartTime'] ?? null;
        $endTime   = $data['slotEndTime'] ?? null;
        $notes     = $data['notes'] ?? null;

        if (!$name || !$phone) {
            throw new BadRequestHttpException('Имя и телефон обязательны');
        }
        if (!$masterId || !$serviceId || !$slotDate || !$startTime || !$endTime) {
            throw new BadRequestHttpException('masterId, serviceId, slotDate, slotStartTime, slotEndTime обязательны');
        }

        $master = $masterRepository->find($masterId);
        if (!$master) {
            throw new NotFoundHttpException('Мастер не найден');
        }

        $service = $serviceRepository->find($serviceId);
        if (!$service) {
            throw new NotFoundHttpException('Услуга не найдена');
        }

        // Создаем гостевого пользователя
        $email = 'guest_' . uniqid() . '@avtomaster.kg';
        $user = $userFactory->create($email, 'guest_' . uniqid());
        $user->setFirstName($name);
        $user->setRoles(['ROLE_USER']);
        $userManager->save($user, true);

        // Создаем бронирование
        $booking = new Booking();
        $booking->setClient($user);
        $booking->setMaster($master);
        $booking->setService($service);
        $booking->setTotal($service->getPrice());
        $booking->setStatus(BookingStatus::PENDING);
        $booking->setSlotDate(new DateTime($slotDate));
        $booking->setSlotStartTime($startTime);
        $booking->setSlotEndTime($endTime);
        $booking->setNotes($phone . ($notes ? ' | ' . $notes : ''));
        $booking->setCreatedAt(new DateTime());

        $em->persist($booking);
        $em->flush();

        // Уведомляем мастера
        $masterUser = $master->getUser();
        if ($masterUser) {
            $notification = (new NotificationBuilder())
                ->bookingCreated($booking)
                ->build($masterUser);
            $em->persist($notification);
            $em->flush();
        }

        return new JsonResponse([
            'data' => [
                'id' => $booking->getId(),
                'status' => $booking->getStatus(),
                'clientName' => $name,
                'clientPhone' => $phone,
                'serviceName' => $service->getName(),
                'total' => $booking->getTotal(),
                'slotDate' => $booking->getSlotDate()->format('Y-m-d'),
                'slotStartTime' => $booking->getSlotStartTime(),
                'slotEndTime' => $booking->getSlotEndTime(),
            ]
        ], Response::HTTP_CREATED);
    }
}
