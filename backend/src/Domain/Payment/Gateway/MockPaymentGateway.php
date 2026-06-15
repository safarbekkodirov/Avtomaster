<?php

namespace App\Domain\Payment\Gateway;

use App\Domain\Payment\DTO\InitiatePaymentDTO;
use App\Domain\Payment\DTO\PaymentResultDTO;
use App\Domain\Payment\DTO\RefundResultDTO;

/**
 * Mock gateway for dev/testing — simulates Stripe without real API calls.
 */
final class MockPaymentGateway implements PaymentGatewayInterface
{
    public function initiate(InitiatePaymentDTO $dto): PaymentResultDTO
    {
        $sessionId = 'cs_test_' . bin2hex(random_bytes(16));
        $paymentIntentId = 'pi_test_' . bin2hex(random_bytes(16));

        return new PaymentResultDTO(
            gatewayPaymentId: $paymentIntentId,
            checkoutUrl:      '/mock-checkout?session_id=' . $sessionId . '&booking_id=' . $dto->bookingId,
            status:           'complete',
            gatewaySessionId: $sessionId,
        );
    }

    public function refund(string $gatewayPaymentId, string $amount, string $currency): RefundResultDTO
    {
        return new RefundResultDTO(
            gatewayRefundId: 're_test_' . bin2hex(random_bytes(8)),
            status:          'succeeded',
            amount:          $amount,
        );
    }

    public function verifyWebhookSignature(string $payload, string $signature): array
    {
        $data = json_decode($payload, true);

        if (!is_array($data) || !isset($data['type'], $data['data']['object'])) {
            throw new \RuntimeException('Invalid webhook payload');
        }

        return $data;
    }
}
