<?php
// src/Domain/Review/Service/ReviewService.php

namespace App\Domain\Review\Service;

use App\Domain\Booking\Entity\Booking;
use App\Domain\Booking\Repository\BookingRepository;
use App\Domain\Review\DTO\CreateReviewRequestDTO;
use App\Domain\Review\DTO\ReviewResponseDTO;
use App\Domain\Review\Entity\Review;
use App\Domain\Review\Repository\ReviewRepository;
use App\Domain\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class ReviewService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ReviewRepository       $reviewRepository,
        private readonly BookingRepository      $bookingRepository,
    ) {}

    public function create(
        int                    $bookingId,
        CreateReviewRequestDTO $dto,
        User                   $client,
    ): ReviewResponseDTO {
        $booking = $this->bookingRepository->find($bookingId);

        if ($booking === null) {
            throw new \DomainException('Бронирование не найдено');
        }

        // Только клиент этого бронирования может оставить отзыв
        if ($booking->getClient()->getId() !== $client->getId()) {
            throw new \DomainException('Нет прав для оставления отзыва');
        }

        // Отзыв только после завершения
        if ($booking->getStatus() !== Booking::STATUS_COMPLETED) {
            throw new \DomainException('Отзыв можно оставить только после завершения услуги');
        }

        // Один отзыв на одно бронирование
        if ($this->reviewRepository->findByBookingId($bookingId) !== null) {
            throw new \DomainException('Отзыв на это бронирование уже оставлен');
        }

        return $this->em->wrapInTransaction(function () use ($booking, $dto): ReviewResponseDTO {
            $review = new Review(
                booking: $booking,
                client:  $booking->getClient(),
                master:  $booking->getMaster(),
                rating:  $dto->rating,
                comment: $dto->comment,
            );

            $this->em->persist($review);

            // Атомарный пересчёт рейтинга через SQL — без race condition
            $this->em->getConnection()->executeStatement(
                'UPDATE masters
                 SET rating       = ROUND(
                     (rating * reviews_count + :newRating) / (reviews_count + 1), 2
                 ),
                 reviews_count = reviews_count + 1
                 WHERE id = :masterId',
                [
                    'newRating' => $dto->rating,
                    'masterId'  => $booking->getMaster()->getId(),
                ]
            );

            $this->em->flush();

            return ReviewResponseDTO::fromEntity($review);
        });
    }

    /**
     * @return array{items: ReviewResponseDTO[], total: int, page: int, perPage: int}
     */
    public function getMasterReviews(int $masterId, int $page, int $perPage): array
    {
        $result = $this->reviewRepository->findVisibleByMaster($masterId, $page, $perPage);

        return [
            'items'   => array_map(ReviewResponseDTO::fromEntity(...), $result['items']),
            'total'   => $result['total'],
            'page'    => $page,
            'perPage' => $perPage,
        ];
    }
}
