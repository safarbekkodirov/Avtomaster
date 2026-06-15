<?php
// src/Domain/Master/Entity/Master.php

namespace App\Domain\Master\Entity;

use App\Domain\User\Entity\User;
use App\Domain\Region\Entity\Region;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'masters')]
#[ORM\Index(columns: ['lat', 'lng'], name: 'idx_location')]
#[ORM\Index(columns: ['rating'], name: 'idx_rating')]
class Master
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private int $id;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Region::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Region $region;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 8, nullable: true)]
    private ?string $lat = null;

    #[ORM\Column(type: 'decimal', precision: 11, scale: 8, nullable: true)]
    private ?string $lng = null;

    #[ORM\Column(type: 'decimal', precision: 3, scale: 2, options: ['default' => '0.00'])]
    private string $rating = '0.00';

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    private int $reviewsCount = 0;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isVerified = false;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    #[ORM\OneToMany(targetEntity: MasterService::class, mappedBy: 'master', cascade: ['persist'])]
    private Collection $services;

    #[ORM\OneToMany(targetEntity: MasterSlot::class, mappedBy: 'master', cascade: ['persist'])]
    private Collection $slots;

    public function __construct()
    {
        $this->services  = new ArrayCollection();
        $this->slots     = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function setUser(User $user): void              { $this->user = $user; }
    public function setRegion(Region $region): void        { $this->region = $region; }
    public function setBio(?string $bio): void             { $this->bio = $bio; }
    public function setAddress(?string $address): void     { $this->address = $address; }
    public function setLat(?string $lat): void             { $this->lat = $lat; }
    public function setLng(?string $lng): void             { $this->lng = $lng; }
    public function setRating(string $rating): void        { $this->rating = $rating; }
    public function setReviewsCount(int $count): void      { $this->reviewsCount = $count; }
    public function setIsVerified(bool $v): void           { $this->isVerified = $v; }
    public function setIsActive(bool $a): void             { $this->isActive = $a; }

    public function getId(): int                          { return $this->id; }
    public function getUser(): User                       { return $this->user; }
    public function getRegion(): Region                   { return $this->region; }
    public function getBio(): ?string                     { return $this->bio; }
    public function getAddress(): ?string                 { return $this->address; }
    public function getLat(): ?string                     { return $this->lat; }
    public function getLng(): ?string                     { return $this->lng; }
    public function getRating(): string                   { return $this->rating; }
    public function getReviewsCount(): int                { return $this->reviewsCount; }
    public function isVerified(): bool                    { return $this->isVerified; }
    public function isActive(): bool                      { return $this->isActive; }
    public function getCreatedAt(): \DateTimeImmutable    { return $this->createdAt; }

    /** @return Collection<MasterService> */
    public function getServices(): Collection             { return $this->services; }

    /** @return Collection<MasterSlot> */
    public function getSlots(): Collection                { return $this->slots; }

    public function recalculateRating(float $newRating): void
    {
        // пересчёт при добавлении отзыва
        $total             = (float) $this->rating * $this->reviewsCount + $newRating;
        $this->reviewsCount++;
        $this->rating      = number_format($total / $this->reviewsCount, 2);
        $this->updatedAt   = new \DateTimeImmutable();
    }
}
