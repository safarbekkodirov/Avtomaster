<?php
// src/Domain/Booking/Event/BookingCreatedEvent.php

namespace App\Domain\Booking\Event;

use App\Domain\Booking\Entity\Booking;

final class BookingCreatedEvent
{
    public function __construct(
        public readonly Booking $booking,
    ) {}
}
