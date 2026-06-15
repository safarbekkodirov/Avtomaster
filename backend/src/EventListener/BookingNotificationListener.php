<?php
// src/EventListener/BookingNotificationListener.php

namespace App\EventListener;

use App\Domain\Booking\Event\BookingCreatedEvent;
use App\Domain\Booking\Event\BookingStatusChangedEvent;
use App\Domain\Booking\Entity\Booking;
use App\Infrastructure\Notification\NotificationServiceInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: BookingCreatedEvent::class)]
#[AsEventListener(event: BookingStatusChangedEvent::class)]
final class BookingNotificationListener
{
    public function __construct(
        private readonly NotificationServiceInterface $notifications,
    ) {}

    public function __invoke(BookingCreatedEvent|BookingStatusChangedEvent $event): void
    {
        $booking = $event->booking;

        match (true) {
            $event instanceof BookingCreatedEvent         => $this->onCreated($booking),
            $event instanceof BookingStatusChangedEvent   => $this->onStatusChanged($booking, $event->previousStatus),
        };
    }

    private function onCreated(Booking $booking): void
    {
        $data = $this->flatten($booking);

        $this->notifications->send(
            recipient: $booking->getClient()->getEmail(),
            template:  'booking.created',
            context:   ['booking' => $data],
        );

        $this->notifications->send(
            recipient: $booking->getMaster()->getUser()->getEmail(),
            template:  'booking.new_request',
            context:   ['booking' => $data],
        );
    }

    private function onStatusChanged(Booking $booking, string $previousStatus): void
    {
        $template = match ($booking->getStatus()) {
            Booking::STATUS_CONFIRMED => 'booking.confirmed',
            Booking::STATUS_CANCELLED => 'booking.cancelled',
            Booking::STATUS_COMPLETED => 'booking.completed',
            default                   => null,
        };

        if ($template === null) {
            return;
        }

        $data = $this->flatten($booking);

        $this->notifications->send(
            recipient: $booking->getClient()->getEmail(),
            template:  $template,
            context:   ['booking' => $data, 'previousStatus' => $previousStatus],
        );
    }

    private function flatten(Booking $booking): array
    {
        return [
            'id'     => $booking->getId(),
            'status' => $booking->getStatus(),
            'total'  => $booking->getTotal(),
            'notes'  => $booking->getNotes(),
            'cancelledReason' => $booking->getCancelledReason(),
            'client' => [
                'firstName' => $booking->getClient()->getProfile()?->getFirstName(),
                'lastName'  => $booking->getClient()->getProfile()?->getLastName(),
            ],
            'master' => [
                'firstName' => $booking->getMaster()->getUser()->getProfile()?->getFirstName(),
                'lastName'  => $booking->getMaster()->getUser()->getProfile()?->getLastName(),
                'address'   => $booking->getMaster()->getAddress(),
            ],
            'service' => [
                'name' => $booking->getService()->getName(),
            ],
            'slot' => [
                'date'      => $booking->getSlot()->getSlotDate()->format('Y-m-d'),
                'startTime' => $booking->getSlot()->getStartTime()->format('H:i'),
                'endTime'   => $booking->getSlot()->getEndTime()->format('H:i'),
            ],
        ];
    }
}
