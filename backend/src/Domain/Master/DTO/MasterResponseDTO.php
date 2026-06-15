<?php
// src/Domain/Master/DTO/MasterResponseDTO.php

namespace App\Domain\Master\DTO;

final class MasterResponseDTO
{
    public function __construct(
        public readonly int     $id,
        public readonly string  $firstName,
        public readonly string  $lastName,
        public readonly ?string $avatar,
        public readonly string  $regionName,
        public readonly ?string $address,
        public readonly float   $rating,
        public readonly int     $reviewsCount,
        public readonly bool    $isVerified,
        public readonly ?float  $distanceKm,
        /** @var MasterServiceDTO[] */
        public readonly array   $services,
    ) {}
}
