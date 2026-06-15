<?php
// src/Domain/Booking/DTO/CancelBookingRequestDTO.php

namespace App\Domain\Booking\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class CancelBookingRequestDTO
{
    public function __construct(
        #[Assert\Length(max: 500)]
        public readonly ?string $reason = null,
    ) {}
}
