<?php
// src/Infrastructure/Notification/NotificationServiceInterface.php

namespace App\Infrastructure\Notification;

interface NotificationServiceInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function send(
        string  $recipient,
        string  $template,
        array   $context   = [],
        ?string $subject   = null,
    ): void;
}
