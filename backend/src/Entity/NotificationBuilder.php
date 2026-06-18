<?php

declare(strict_types=1);

namespace App\Entity;

class NotificationBuilder
{
    private string $type;
    private string $title;
    private string $body;
    private ?int $relatedId = null;
    private ?string $relatedType = null;

    public static function forUser(User $user): self
    {
        $builder = new self();
        return $builder;
    }

    public function bookingCreated(Booking $booking): self
    {
        $this->type = Notification::TYPE_BOOKING_CREATED;
        $this->title = 'Новая запись';
        $this->body = sprintf(
            'Вы записались к %s %s на %s',
            $booking->getMasterFirstName(),
            $booking->getMasterLastName(),
            $booking->getSlotDate()->format('d.m.Y')
        );
        $this->relatedId = $booking->getId();
        $this->relatedType = 'booking';
        return $this;
    }

    public function bookingConfirmed(Booking $booking): self
    {
        $this->type = Notification::TYPE_BOOKING_CONFIRMED;
        $this->title = 'Запись подтверждена';
        $this->body = sprintf(
            '%s %s подтвердил вашу запись на %s',
            $booking->getMasterFirstName(),
            $booking->getMasterLastName(),
            $booking->getSlotDate()->format('d.m.Y')
        );
        $this->relatedId = $booking->getId();
        $this->relatedType = 'booking';
        return $this;
    }

    public function bookingCompleted(Booking $booking): self
    {
        $this->type = Notification::TYPE_BOOKING_COMPLETED;
        $this->title = 'Запись завершена';
        $this->body = sprintf(
            'Услуга "%s" у %s %s выполнена',
            $booking->getServiceName(),
            $booking->getMasterFirstName(),
            $booking->getMasterLastName()
        );
        $this->relatedId = $booking->getId();
        $this->relatedType = 'booking';
        return $this;
    }

    public function bookingCancelled(Booking $booking): self
    {
        $this->type = Notification::TYPE_BOOKING_CANCELLED;
        $this->title = 'Запись отменена';
        $this->body = sprintf(
            'Запись к %s %s на %s отменена',
            $booking->getMasterFirstName(),
            $booking->getMasterLastName(),
            $booking->getSlotDate()->format('d.m.Y')
        );
        $this->relatedId = $booking->getId();
        $this->relatedType = 'booking';
        return $this;
    }

    public function paymentReceived(Booking $booking, string $amount): self
    {
        $this->type = Notification::TYPE_PAYMENT_RECEIVED;
        $this->title = 'Оплата получена';
        $this->body = sprintf('Получена оплата %s ₽ за услугу "%s"', $amount, $booking->getServiceName());
        $this->relatedId = $booking->getId();
        $this->relatedType = 'booking';
        return $this;
    }

    public function reviewReceived(Booking $booking, int $rating): self
    {
        $this->type = Notification::TYPE_REVIEW_RECEIVED;
        $this->title = 'Новый отзыв';
        $this->body = sprintf('Оставлена оценка %d/5 за услугу "%s"', $rating, $booking->getServiceName());
        $this->relatedId = $booking->getId();
        $this->relatedType = 'booking';
        return $this;
    }

    public function build(User $user): Notification
    {
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setType($this->type);
        $notification->setTitle($this->title);
        $notification->setBody($this->body);
        $notification->setRelatedId($this->relatedId);
        $notification->setRelatedType($this->relatedType);
        $notification->setIsRead(false);
        $notification->setCreatedAt(new \DateTime());

        return $notification;
    }
}
