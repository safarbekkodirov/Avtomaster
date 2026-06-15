<?php
// src/Domain/Payment/Gateway/PaymentGatewayInterface.php

namespace App\Domain\Payment\Gateway;

use App\Domain\Payment\DTO\InitiatePaymentDTO;
use App\Domain\Payment\DTO\PaymentResultDTO;
use App\Domain\Payment\DTO\RefundResultDTO;

interface PaymentGatewayInterface
{
    public function initiate(InitiatePaymentDTO $dto): PaymentResultDTO;

    public function refund(string $gatewayPaymentId, string $amount, string $currency): RefundResultDTO;

    /**
     * Верификация webhook-подписи — защита от поддельных событий
     *
     * @throws \RuntimeException если подпись невалидна
     */
    public function verifyWebhookSignature(string $payload, string $signature): array;
}
