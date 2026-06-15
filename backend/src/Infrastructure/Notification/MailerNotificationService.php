<?php
// src/Infrastructure/Notification/MailerNotificationService.php

namespace App\Infrastructure\Notification;

use App\Infrastructure\Notification\Message\SendNotificationMessage;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Диспатчит уведомления в очередь — не отправляет напрямую.
 * Реальная отправка происходит в SendNotificationHandler.
 */
final class MailerNotificationService implements NotificationServiceInterface
{
    public function __construct(
        private readonly MessageBusInterface $bus,
    ) {}

    public function send(
        string  $recipient,
        string  $template,
        array   $context   = [],
        ?string $subject   = null,
    ): void {
        $this->bus->dispatch(new SendNotificationMessage(
            recipient: $recipient,
            template:  $template,
            context:   $context,
            subject:   $subject,
        ));
    }
}
