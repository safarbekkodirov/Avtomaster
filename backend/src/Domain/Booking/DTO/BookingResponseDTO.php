<?php
// src/Domain/Booking/DTO/BookingResponseDTO.php

namespace App\Domain\Booking\DTO;

final class BookingResponseDTO
{
    public function __construct(
        public readonly int     $id,
        public readonly string  $status,
        public readonly string  $total,
        public readonly string  $serviceName,
        public readonly int     $serviceDuration,
        public readonly string  $masterFirstName,
        public readonly string  $masterLastName,
        public readonly string  $slotDate,
        public readonly string  $slotStartTime,
        public readonly string  $slotEndTime,
        public readonly ?string $notes,
        public readonly ?string $cancelledReason,
        public readonly string  $createdAt,
    ) {}

    public static function fromEntity(
        \App\Domain\Booking\Entity\Booking $booking
    ): self {
        $slot    = $booking->getSlot();
        $master  = $booking->getMaster()->getUser()->getProfile();
        $service = $booking->getService();

        return new self(
            id:              $booking->getId(),
            status:          $booking->getStatus(),
            total:           $booking->getTotal(),
            serviceName:     $service->getName(),
            serviceDuration: $service->getDuration(),
            masterFirstName: $master?->getFirstName() ?? '',
            masterLastName:  $master?->getLastName()  ?? '',
            slotDate:        $slot->getSlotDate()->format('Y-m-d'),
            slotStartTime:   $slot->getStartTime()->format('H:i'),
            slotEndTime:     $slot->getEndTime()->format('H:i'),
            notes:           $booking->getNotes(),
            cancelledReason: $booking->getCancelledReason(),
            createdAt:       $booking->getCreatedAt()->format(\DateTimeInterface::ATOM),
        );
    }
}
