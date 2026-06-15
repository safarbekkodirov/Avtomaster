<?php
// src/Domain/Review/DTO/ReviewResponseDTO.php

namespace App\Domain\Review\DTO;

use App\Domain\Review\Entity\Review;

final class ReviewResponseDTO
{
    public function __construct(
        public readonly int     $id,
        public readonly int     $bookingId,
        public readonly string  $clientFirstName,
        public readonly string  $clientLastName,
        public readonly ?string $clientAvatar,
        public readonly int     $rating,
        public readonly ?string $comment,
        public readonly string  $createdAt,
    ) {}

    public static function fromEntity(Review $review): self
    {
        $profile = $review->getClient()->getProfile();

        return new self(
            id:              $review->getId(),
            bookingId:       $review->getBooking()->getId(),
            clientFirstName: $profile?->getFirstName() ?? '',
            clientLastName:  $profile?->getLastName()  ?? '',
            clientAvatar:    $profile?->getAvatar(),
            rating:          $review->getRating(),
            comment:         $review->getComment(),
            createdAt:       $review->getCreatedAt()->format(\DateTimeInterface::ATOM),
        );
    }
}
