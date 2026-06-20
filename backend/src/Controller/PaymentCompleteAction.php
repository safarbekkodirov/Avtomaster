<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractController;
use App\Entity\Payment;
use App\Repository\BookingRepository;
use App\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/bookings/{bookingId}/payment/complete', methods: ['POST'])]
class PaymentCompleteAction extends AbstractController
{
    public function __invoke(
        int $bookingId,
        Request $request,
        BookingRepository $bookingRepository,
        PaymentRepository $paymentRepository,
        EntityManagerInterface $em,
    ): JsonResponse {
        $booking = $bookingRepository->find($bookingId);
        if (!$booking) {
            throw new NotFoundHttpException('Booking not found');
        }

        if ($booking->getClient()?->getId() !== $this->getUser()->getId()) {
            throw new BadRequestHttpException('You can only complete payments for your own bookings');
        }

        $payment = $paymentRepository->findOneByBookingId($bookingId);
        if (!$payment) {
            throw new NotFoundHttpException('Payment not found');
        }

        if ($payment->getStatus() === Payment::STATUS_PAID) {
            return new JsonResponse(['data' => ['status' => 'already_paid']], 200);
        }

        $payment->setStatus(Payment::STATUS_PAID);
        $payment->setUpdatedAt(new DateTime());

        $em->flush();

        $serialized = $this->getSerializer()->serialize(['data' => $payment], 'json', [
            'groups' => ['payment:read'],
        ]);

        return new JsonResponse(json_decode($serialized, true), 200);
    }
}
