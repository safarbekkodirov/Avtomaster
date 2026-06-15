<?php
// src/Domain/Admin/Service/AdminBookingService.php

namespace App\Domain\Admin\Service;

use App\Domain\Booking\Entity\Booking;
use App\Domain\Booking\Repository\BookingRepository;
use App\Domain\Payment\Service\PaymentService;
use App\Domain\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class AdminBookingService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly BookingRepository      $bookingRepository,
        private readonly PaymentService         $paymentService,
        private readonly AuditService           $audit,
    ) {}

    /**
     * Принудительная отмена с опциональным возвратом средств
     */
    public function forceCancel(
        int    $bookingId,
        User   $admin,
        string $reason,
        bool   $withRefund = false,
    ): void {
        $booking = $this->getBookingOrFail($bookingId);

        if ($withRefund && $booking->getStatus() === Booking::STATUS_CONFIRMED) {
            $this->paymentService->refund($bookingId);
        }

        $before = ['status' => $booking->getStatus()];
        $booking->cancel($reason);

        // При отмене администратором слот освобождается всегда
        if ($booking->getSlot()->getStatus() !== 'available') {
            $booking->getSlot()->release();
        }

        $after = ['status' => $booking->getStatus()];

        $this->audit->log(
            $admin,
            $booking,
            'force_cancel',
            array_merge($this->audit->diff($before, $after), ['reason' => $reason])
        );

        $this->em->flush();
    }

    private function getBookingOrFail(int $id): Booking
    {
        $booking = $this->bookingRepository->find($id);
        if ($booking === null) {
            throw new \DomainException('Бронирование не найдено');
        }
        return $booking;
    }
}
