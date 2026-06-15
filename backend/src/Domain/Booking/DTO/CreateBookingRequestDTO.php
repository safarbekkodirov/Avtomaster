<?php
// src/Domain/Booking/DTO/CreateBookingRequestDTO.php

namespace App\Domain\Booking\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateBookingRequestDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Positive]
        public readonly int $masterId,

        #[Assert\NotBlank]
        #[Assert\Positive]
        public readonly int $slotId,

        #[Assert\NotBlank]
        #[Assert\Positive]
        public readonly int $serviceId,

        #[Assert\Length(max: 500)]
        public readonly ?string $notes = null,
    ) {}
}
