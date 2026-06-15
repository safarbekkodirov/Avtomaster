<?php
// src/Domain/Payment/Service/WebhookService.php

namespace App\Domain\Payment\Service;

use App\Domain\Booking\Entity\Booking;
use App\Domain\Payment\Entity\PaymentWebhookEvent;
use App\Domain\Payment\Gateway\PaymentGatewayInterface;
use App\Domain\Payment\Message\ProcessWebhookEventMessage;
use App\Domain\Payment\Repository\PaymentRepository;
use App\Domain\Payment\Repository\PaymentWebhookEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class WebhookService
{
    public function __construct(
        private readonly EntityManagerInterface       $em,
        private readonly PaymentGatewayInterface      $gateway,
        private readonly PaymentRepository            $paymentRepository,
        private readonly PaymentWebhookEventRepository $webhookEventRepository,
        private readonly MessageBusInterface          $bus,
    ) {}

    /**
     * Точка входа — вызывается из контроллера.
     * Верифицируем подпись → сохраняем событие → диспатчим в очередь.
     * Контроллер отвечает 200 сразу, обработка асинхронна.
     */
    public function handleIncoming(string $rawPayload, string $signature): void
    {
        // Верификация подписи Stripe — бросит RuntimeException при невалидной
        $eventData = $this->gateway->verifyWebhookSignature($rawPayload, $signature);

        $gatewayEventId = $eventData['id'];

        // Идемпотентность — одно событие обрабатываем ровно один раз
        if ($this->webhookEventRepository->findByGatewayEventId($gatewayEventId) !== null) {
            return;
        }

        $event = new PaymentWebhookEvent(
            gatewayEventId: $gatewayEventId,
            type:           $eventData['type'],
            payload:        $eventData,
        );

        $this->em->persist($event);
        $this->em->flush();

        // Асинхронная обработка через Messenger
        $this->bus->dispatch(new ProcessWebhookEventMessage($event->getId()));
    }

    /**
     * Обработчик вызывается из MessageHandler (воркер).
     * Изолирован от HTTP-слоя — безопасно ретраить.
     */
    public function processEvent(int $webhookEventId): void
    {
        $event = $this->webhookEventRepository->find($webhookEventId);

        if ($event === null || $event->getStatus() === PaymentWebhookEvent::STATUS_PROCESSED) {
            return;
        }

        try {
            $this->em->wrapInTransaction(function () use ($event): void {
                $this->dispatchByType($event);
                $event->markProcessed();
            });
        } catch (\Throwable $e) {
            $event->markFailed();
            $this->em->flush();
            throw $e; // Messenger поймает и запустит retry
        }
    }

    private function dispatchByType(PaymentWebhookEvent $event): void
    {
        $payload = $event->getPayload();

        match ($event->getType()) {
            'checkout.session.completed'       => $this->onCheckoutCompleted($payload),
            'checkout.session.expired'         => $this->onCheckoutExpired($payload),
            'payment_intent.payment_failed'    => $this->onPaymentFailed($payload),
            'charge.refunded'                  => $this->onRefunded($payload),
            default                            => null, // неизвестные события игнорируем
        };
    }

    private function onCheckoutCompleted(array $payload): void
    {
        $sessionId     = $payload['data']['object']['id'];
        $paymentIntent = $payload['data']['object']['payment_intent'];

        $payment = $this->paymentRepository->findByGatewaySessionId($sessionId);

        if ($payment === null) {
            throw new \RuntimeException("Payment не найден для session: {$sessionId}");
        }

        if ($payment->isPaid()) {
            return; // Идемпотентность
        }

        $payment->markAsPaid($paymentIntent, $payload['data']['object']);

        // Автоподтверждение бронирования после оплаты
        $booking = $payment->getBooking();
        if ($booking->getStatus() === Booking::STATUS_PENDING) {
            $booking->confirm();
        }

        $this->em->flush();
    }

    private function onCheckoutExpired(array $payload): void
    {
        $sessionId = $payload['data']['object']['id'];
        $payment   = $this->paymentRepository->findByGatewaySessionId($sessionId);

        if ($payment === null || !$payment->isPending()) {
            return;
        }

        $payment->markAsFailed($payload['data']['object']);

        // Отменяем бронирование и освобождаем слот
        $booking = $payment->getBooking();
        if (in_array($booking->getStatus(), [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED], true)) {
            $booking->cancel('Сессия оплаты истекла');
            $booking->getSlot()->release();
        }

        $this->em->flush();
    }

    private function onPaymentFailed(array $payload): void
    {
        $paymentIntentId = $payload['data']['object']['id'];

        // Ищем по gateway_payment_id если уже был установлен
        $payment = $this->paymentRepository->findOneBy(['gatewayPaymentId' => $paymentIntentId]);

        if ($payment === null) {
            return;
        }

        $payment->markAsFailed($payload['data']['object']);
        $this->em->flush();
    }

    private function onRefunded(array $payload): void
    {
        $paymentIntentId = $payload['data']['object']['payment_intent'];
        $payment         = $this->paymentRepository->findOneBy(['gatewayPaymentId' => $paymentIntentId]);

        if ($payment === null || $payment->getStatus() === Payment::STATUS_REFUNDED) {
            return;
        }

        $refundId = $payload['data']['object']['refunds']['data'][0]['id'] ?? '';
        $payment->markAsRefunded($refundId);

        // Освобождаем слот если бронирование отменено
        $booking = $payment->getBooking();
        if ($booking->getStatus() === Booking::STATUS_CANCELLED) {
            $booking->getSlot()->release();
        }

        $this->em->flush();
    }
}
