<?php
// src/Domain/Payment/MessageHandler/ProcessWebhookEventHandler.php

namespace App\Domain\Payment\MessageHandler;

use App\Domain\Payment\Message\ProcessWebhookEventMessage;
use App\Domain\Payment\Service\WebhookService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;

#[AsMessageHandler]
final class ProcessWebhookEventHandler
{
    public function __construct(
        private readonly WebhookService $webhookService,
    ) {}

    public function __invoke(ProcessWebhookEventMessage $message): void
    {
        try {
            $this->webhookService->processEvent($message->webhookEventId);
        } catch (\DomainException $e) {
            // DomainException — не ретраим, бизнес-ошибка
            throw $e;
        } catch (\Throwable $e) {
            // Сетевые/временные ошибки — Messenger сделает retry
            throw new RecoverableMessageHandlingException($e->getMessage(), previous: $e);
        }
    }
}
