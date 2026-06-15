<?php
// src/Domain/Review/DTO/CreateReviewRequestDTO.php

namespace App\Domain\Review\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateReviewRequestDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Range(min: 1, max: 5)]
        public readonly int $rating,

        #[Assert\Length(max: 1000)]
        public readonly ?string $comment = null,
    ) {}
}
