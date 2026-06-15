<?php
// src/Domain/Payment/DTO/PaymentResponseDTO.php

namespace App\Domain\Payment\DTO;

use App\Domain\Payment\Entity\Payment;

final class PaymentResponseDTO
{
    public function __construct(
        public readonly int     $id,
        public readonly int     $bookingId,
        public readonly string  $status,
        public readonly string  $amount,
        public readonly string  $currency,
        public readonly ?string $checkoutUrl,
        public readonly string  $createdAt,
    ) {}

    public static function fromEntity(Payment $payment): self
    {
        return new self(
            id:          $payment->getId(),
            bookingId:   $payment->getBooking()->getId(),
            status:      $payment->getStatus(),
            amount:      $payment->getAmount(),
            currency:    $payment->getCurrency(),
            checkoutUrl: $payment->getCheckoutUrl(),
            createdAt:   $payment->getCreatedAt()->format(\DateTimeInterface::ATOM),
        );
    }
}
