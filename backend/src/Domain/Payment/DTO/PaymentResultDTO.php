<?php
// src/Domain/Payment/DTO/PaymentResultDTO.php

namespace App\Domain\Payment\DTO;

final class PaymentResultDTO
{
    public function __construct(
        public readonly string  $gatewayPaymentId,   // stripe payment_intent id
        public readonly string  $checkoutUrl,         // URL для редиректа клиента
        public readonly string  $status,
        public readonly ?string $gatewaySessionId = null,
    ) {}
}
