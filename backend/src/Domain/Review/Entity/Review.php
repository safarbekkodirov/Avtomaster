<?php
// src/Domain/Review/Entity/Review.php

namespace App\Domain\Review\Entity;

use App\Domain\Booking\Entity\Booking;
use App\Domain\Master\Entity\Master;
use App\Domain\User\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'reviews')]
#[ORM\Index(columns: ['master_id', 'is_visible'], name: 'idx_master_visible')]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private int $id;

    // OneToOne — один отзыв на одно бронирование, гарантировано на уровне БД
    #[ORM\OneToOne(targetEntity: Booking::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE', unique: true)]
    private Booking $booking;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $client;

    #[ORM\ManyToOne(targetEntity: Master::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Master $master;

    #[ORM\Column(type: 'smallint')]
    private int $rating; // 1–5

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isVisible = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        Booking $booking,
        User    $client,
        Master  $master,
        int     $rating,
        ?string $comment = null,
    ) {
        if ($rating < 1 || $rating > 5) {
            throw new \DomainException('Рейтинг должен быть от 1 до 5');
        }

        $this->booking   = $booking;
        $this->client    = $client;
        $this->master    = $master;
        $this->rating    = $rating;
        $this->comment   = $comment;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function hide(): void { $this->isVisible = false; }
    public function show(): void { $this->isVisible = true; }

    public function getId(): int                       { return $this->id; }
    public function getBooking(): Booking              { return $this->booking; }
    public function getClient(): User                  { return $this->client; }
    public function getMaster(): Master                { return $this->master; }
    public function getRating(): int                   { return $this->rating; }
    public function getComment(): ?string              { return $this->comment; }
    public function isVisible(): bool                  { return $this->isVisible; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
