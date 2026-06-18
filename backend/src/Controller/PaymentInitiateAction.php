<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractController;
use App\Controller\Base\Constants\BookingStatus;
use App\Entity\Booking;
use App\Entity\NotificationBuilder;
use App\Entity\Payment;
use App\Repository\BookingRepository;
use App\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/bookings/{bookingId}/payment', methods: ['POST'])]
class PaymentInitiateAction extends AbstractController
{
    public function __invoke(
        int $bookingId,
        BookingRepository $bookingRepository,
        PaymentRepository $paymentRepository,
        EntityManagerInterface $em,
    ): Response {
        $booking = $bookingRepository->find($bookingId);
        if (!$booking) {
            throw new NotFoundHttpException('Booking not found');
        }

        if ($booking->getClient()?->getId() !== $this->getUser()->getId()) {
            throw new BadRequestHttpException('You can only pay for your own bookings');
        }

        $existingPayment = $paymentRepository->findOneByBookingId($bookingId);
        if ($existingPayment && $existingPayment->getStatus() === Payment::STATUS_PAID) {
            throw new BadRequestHttpException('Booking is already paid');
        }

        $payment = $existingPayment ?? new Payment();
        $payment->setBooking($booking);
        $payment->setAmount($booking->getTotal());
        $payment->setCurrency('KGS');
        $payment->setStatus(Payment::STATUS_PENDING);
        $payment->setExternalId('mock_' . uniqid());
        $payment->setCheckoutUrl(
            '/payment/' . $bookingId . '?status=success'
        );
        $payment->setCreatedAt(new DateTime());

        $em->persist($payment);
        $em->flush();

        $notification = (new NotificationBuilder())
            ->paymentReceived($booking, $booking->getTotal())
            ->build($this->getUser());
        $em->persist($notification);
        $em->flush();

        $serialized = $this->getSerializer()->serialize(['data' => $payment], 'json', [
            'groups' => ['payment:read'],
        ]);

        return new Response($serialized, Response::HTTP_CREATED, [
            'Content-Type' => 'application/json',
        ]);
    }
}
