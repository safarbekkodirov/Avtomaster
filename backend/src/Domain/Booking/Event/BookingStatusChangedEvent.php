<?php
// src/Domain/Booking/Event/BookingStatusChangedEvent.php

namespace App\Domain\Booking\Event;

use App\Domain\Booking\Entity\Booking;

final class BookingStatusChangedEvent
{
    public function __construct(
        public readonly Booking $booking,
        public readonly string  $previousStatus,
    ) {}
}
