<?php
// src/Domain/Payment/Message/ProcessWebhookEventMessage.php

namespace App\Domain\Payment\Message;

final class ProcessWebhookEventMessage
{
    public function __construct(
        public readonly int $webhookEventId,
    ) {}
}
