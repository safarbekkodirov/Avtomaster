<?php
// src/Domain/Payment/DTO/InitiatePaymentDTO.php

namespace App\Domain\Payment\DTO;

final class InitiatePaymentDTO
{
    public function __construct(
        public readonly int    $bookingId,
        public readonly string $amount,       // decimal string, не float
        public readonly string $currency,
        public readonly string $description,
        public readonly string $idempotencyKey,
        public readonly string $successUrl,
        public readonly string $cancelUrl,
        public readonly string $customerEmail,
    ) {}
}
