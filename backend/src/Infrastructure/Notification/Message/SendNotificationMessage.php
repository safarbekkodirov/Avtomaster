<?php
// src/Infrastructure/Notification/Message/SendNotificationMessage.php

namespace App\Infrastructure\Notification\Message;

final class SendNotificationMessage
{
    public function __construct(
        public readonly string  $recipient,
        public readonly string  $template,
        public readonly array   $context,
        public readonly ?string $subject,
    ) {}
}
