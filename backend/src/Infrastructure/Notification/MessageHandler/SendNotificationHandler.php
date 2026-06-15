<?php
// src/Infrastructure/Notification/MessageHandler/SendNotificationHandler.php

namespace App\Infrastructure\Notification\MessageHandler;

use App\Infrastructure\Notification\Message\SendNotificationMessage;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Address;

#[AsMessageHandler]
final class SendNotificationHandler
{
    // Темы писем по шаблону
    private const SUBJECTS = [
        'booking.created'    => 'Ваша запись создана — ожидает подтверждения',
        'booking.confirmed'  => 'Запись подтверждена мастером',
        'booking.cancelled'  => 'Запись отменена',
        'booking.completed'  => 'Как прошло обслуживание?',
        'booking.new_request'=> 'Новая заявка на запись',
        'auth.verify_email'  => 'Подтвердите email — Avtomaster',
    ];

    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string          $senderEmail,
        private readonly string          $senderName,
    ) {}

    public function __invoke(SendNotificationMessage $message): void
    {
        $subject  = $message->subject ?? self::SUBJECTS[$message->template] ?? 'Уведомление Avtomaster';
        $parts    = explode('.', $message->template);
        $template = 'emails/' . implode('/', $parts) . '.html.twig';

        $email = (new TemplatedEmail())
            ->from(new Address($this->senderEmail, $this->senderName))
            ->to($message->recipient)
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($message->context);

        $this->mailer->send($email);
    }
}
