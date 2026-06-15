<?php
// src/Domain/Master/DTO/MasterSearchRequestDTO.php

namespace App\Domain\Master\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class MasterSearchRequestDTO
{
    public function __construct(
        #[Assert\Length(min: 2, max: 100)]
        public readonly ?string $regionSlug = null,

        #[Assert\Range(min: -90, max: 90)]
        public readonly ?float $lat = null,

        #[Assert\Range(min: -180, max: 180)]
        public readonly ?float $lng = null,

        #[Assert\Range(min: 1, max: 100)]
        public readonly ?float $radiusKm = 10,

        #[Assert\Positive]
        public readonly ?int $categoryId = null,

        #[Assert\Range(min: 1, max: 5)]
        public readonly ?float $minRating = null,

        #[Assert\Range(min: 0)]
        public readonly ?float $maxPrice = null,

        #[Assert\Choice(choices: ['rating', 'distance', 'price'])]
        public readonly string $sortBy = 'rating',

        #[Assert\Range(min: 1, max: 100)]
        public readonly int $perPage = 20,

        #[Assert\Positive]
        public readonly int $page = 1,
    ) {}
}
