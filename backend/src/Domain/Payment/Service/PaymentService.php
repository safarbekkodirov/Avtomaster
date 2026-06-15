<?php
// src/Domain/Payment/Service/PaymentService.php

namespace App\Domain\Payment\Service;

use App\Domain\Booking\Entity\Booking;
use App\Domain\Booking\Repository\BookingRepository;
use App\Domain\Payment\DTO\InitiatePaymentDTO;
use App\Domain\Payment\DTO\PaymentResponseDTO;
use App\Domain\Payment\Entity\Payment;
use App\Domain\Payment\Gateway\PaymentGatewayInterface;
use App\Domain\Payment\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final class PaymentService
{
    public function __construct(
        private readonly EntityManagerInterface  $em,
        private readonly PaymentRepository       $paymentRepository,
        private readonly BookingRepository       $bookingRepository,
        private readonly PaymentGatewayInterface $gateway,
        private readonly string                  $successUrl,
        private readonly string                  $cancelUrl,
    ) {}

    /**
     * Инициализация оплаты — создаёт Payment entity и Stripe Checkout Session
     */
    public function initiate(int $bookingId, string $customerEmail): PaymentResponseDTO
    {
        $booking = $this->bookingRepository->find($bookingId);

        if ($booking === null) {
            throw new \DomainException('Бронирование не найдено');
        }

        if ($booking->getStatus() !== Booking::STATUS_PENDING) {
            throw new \DomainException('Оплата возможна только для ожидающих бронирований');
        }

        // Проверяем — нет ли уже активной оплаты
        $existing = $this->paymentRepository->findByBookingId($bookingId);
        if ($existing !== null && $existing->isPaid()) {
            throw new \DomainException('Бронирование уже оплачено');
        }

        // Идемпотентный ключ — детерминированный из bookingId
        // При повторном запросе Stripe вернёт ту же сессию
        $idempotencyKey = hash('sha256', 'booking_' . $bookingId . '_initiate');

        $dto = new InitiatePaymentDTO(
            bookingId:      $bookingId,
            amount:         $booking->getTotal(),
            currency:       'RUB',
            description:    sprintf(
                'Запись к мастеру: %s, %s %s',
                $booking->getService()->getName(),
                $booking->getSlot()->getSlotDate()->format('d.m.Y'),
                $booking->getSlot()->getStartTime()->format('H:i'),
            ),
            idempotencyKey: $idempotencyKey,
            successUrl:     $this->successUrl . '/' . $bookingId,
            cancelUrl:      $this->cancelUrl  . '/' . $bookingId,
            customerEmail:  $customerEmail,
        );

        $result = $this->gateway->initiate($dto);

        // Создаём или обновляем Payment entity
        $payment = $existing ?? new Payment($booking, $booking->getTotal(), 'RUB');
        $payment->setGatewaySession($result->gatewaySessionId, $result->checkoutUrl);

        $this->em->persist($payment);
        $this->em->flush();

        return PaymentResponseDTO::fromEntity($payment);
    }

    /**
     * Возврат — вызывается из BookingService при отмене подтверждённого бронирования
     */
    public function refund(int $bookingId): void
    {
        $payment = $this->paymentRepository->findByBookingId($bookingId);

        if ($payment === null || !$payment->isPaid()) {
            throw new \DomainException('Нет оплаченного платежа для возврата');
        }

        $result = $this->gateway->refund(
            gatewayPaymentId: $payment->getGatewayPaymentId(),
            amount:           $payment->getAmount(),
            currency:         $payment->getCurrency(),
        );

        $payment->markAsRefunded($result->gatewayRefundId);
        $this->em->flush();
    }
}
