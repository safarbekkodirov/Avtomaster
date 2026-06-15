<?php
// src/Domain/Master/DTO/SlotSearchRequestDTO.php

namespace App\Domain\Master\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class SlotSearchRequestDTO
{
    public function __construct(
        #[Assert\Date]
        public readonly ?string $dateFrom = null,

        #[Assert\Date]
        public readonly ?string $dateTo = null,

        #[Assert\Positive]
        public readonly ?int $serviceId = null,
    ) {}
}
