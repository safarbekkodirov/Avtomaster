<?php
// src/Controller/Api/V1/Payment/PaymentController.php

namespace App\Controller\Api\V1\Payment;

use App\Domain\Payment\Service\PaymentService;
use App\Domain\Payment\Service\WebhookService;
use App\Domain\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/v1', name: 'api_v1_payment_')]
final class PaymentController extends AbstractController
{
    public function __construct(
        private readonly PaymentService  $paymentService,
        private readonly WebhookService  $webhookService,
    ) {}

    /**
     * POST /api/v1/bookings/{bookingId}/payment
     * Инициализация оплаты — возвращает checkout URL для редиректа
     */
    #[Route('/bookings/{bookingId}/payment', name: 'initiate', methods: ['POST'], requirements: ['bookingId' => '\d+'])]
    public function initiate(
        int $bookingId,
        #[CurrentUser] User $user,
    ): JsonResponse {
        $result = $this->paymentService->initiate($bookingId, $user->getEmail());

        return $this->json(['data' => $result], Response::HTTP_CREATED);
    }

    /**
     * POST /api/v1/payment/webhook
     * Webhook от Stripe — публичный endpoint, верификация через подпись
     * ВАЖНО: выведен из-под JWT middleware в security.yaml
     */
    #[Route('/payment/webhook', name: 'webhook', methods: ['POST'])]
    public function webhook(Request $request): Response
    {
        $signature = $request->headers->get('Stripe-Signature', '');

        if (empty($signature)) {
            return new Response('Missing signature', Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->webhookService->handleIncoming(
                rawPayload: $request->getContent(),
                signature:  $signature,
            );
        } catch (\RuntimeException $e) {
            return new Response('Invalid signature', Response::HTTP_UNAUTHORIZED);
        }

        return new Response('', Response::HTTP_OK);
    }
}
