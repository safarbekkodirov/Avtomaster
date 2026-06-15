<?php
// src/Domain/Payment/Gateway/StripeGateway.php

namespace App\Domain\Payment\Gateway;

use App\Domain\Payment\DTO\InitiatePaymentDTO;
use App\Domain\Payment\DTO\PaymentResultDTO;
use App\Domain\Payment\DTO\RefundResultDTO;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;

final class StripeGateway implements PaymentGatewayInterface
{
    public function __construct(
        private readonly StripeClient $stripe,
        private readonly string       $webhookSecret,
    ) {}

    public function initiate(InitiatePaymentDTO $dto): PaymentResultDTO
    {
        // Stripe Checkout Session — готовый hosted UI, не нужно верстать форму
        $session = $this->stripe->checkout->sessions->create(
            [
                'payment_method_types' => ['card'],
                'line_items'           => [
                    [
                        'price_data' => [
                            'currency'     => strtolower($dto->currency),
                            'unit_amount'  => $this->toStripeAmount($dto->amount, $dto->currency),
                            'product_data' => ['name' => $dto->description],
                        ],
                        'quantity' => 1,
                    ],
                ],
                'mode'              => 'payment',
                'success_url'       => $dto->successUrl . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'        => $dto->cancelUrl,
                'customer_email'    => $dto->customerEmail,
                'metadata'          => ['booking_id' => $dto->bookingId],
                'expires_at'        => (new \DateTimeImmutable('+30 minutes'))->getTimestamp(),
            ],
            // Idempotency key — Stripe не создаст дубль при повторном запросе
            ['idempotency_key' => $dto->idempotencyKey]
        );

        return new PaymentResultDTO(
            gatewayPaymentId: $session->payment_intent ?? '',
            checkoutUrl:      $session->url,
            status:           $session->status,
            gatewaySessionId: $session->id,
        );
    }

    public function refund(string $gatewayPaymentId, string $amount, string $currency): RefundResultDTO
    {
        $refund = $this->stripe->refunds->create([
            'payment_intent' => $gatewayPaymentId,
            'amount'         => $this->toStripeAmount($amount, $currency),
        ]);

        return new RefundResultDTO(
            gatewayRefundId: $refund->id,
            status:          $refund->status,
            amount:          (string) ($refund->amount / 100),
        );
    }

    public function verifyWebhookSignature(string $payload, string $signature): array
    {
        try {
            $event = Webhook::constructEvent($payload, $signature, $this->webhookSecret);
        } catch (SignatureVerificationException $e) {
            throw new \RuntimeException('Невалидная подпись webhook: ' . $e->getMessage());
        }

        return $event->toArray();
    }

    /**
     * Stripe принимает суммы в минимальных единицах валюты (копейки, центы)
     */
    private function toStripeAmount(string $amount, string $currency): int
    {
        // Валюты без дробной части (JPY, KRW и др.)
        $zeroDecimalCurrencies = ['JPY', 'KRW', 'VND'];
        if (in_array(strtoupper($currency), $zeroDecimalCurrencies, true)) {
            return (int) $amount;
        }

        return (int) round((float) $amount * 100);
    }
}
