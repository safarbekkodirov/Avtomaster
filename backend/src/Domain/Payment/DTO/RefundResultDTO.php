<?php
// src/Domain/Payment/DTO/RefundResultDTO.php

namespace App\Domain\Payment\DTO;

final class RefundResultDTO
{
    public function __construct(
        public readonly string $gatewayRefundId,
        public readonly string $status,
        public readonly string $amount,
    ) {}
}
