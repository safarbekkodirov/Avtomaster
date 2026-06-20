<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Controller\BookingCreateAction;
use App\Controller\BookingConfirmAction;
use App\Controller\BookingCompleteAction;
use App\Controller\BookingCancelAction;
use App\Controller\MasterBookingsAction;
use App\Entity\Interfaces\CreatedAtSettableInterface;
use App\Entity\Interfaces\DeletedAtSettableInterface;
use App\Entity\Interfaces\UpdatedAtSettableInterface;
use App\Entity\Traits\CreatedAtAccessorsTrait;
use App\Entity\Traits\DeletedAtAndByAccessorsTrait;
use App\Entity\Traits\UpdatedAtAndByAccessorsTrait;
use App\Repository\BookingRepository;
use App\Controller\Base\Constants\BookingStatus;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['booking:list']],
        ),
        new Get(
            uriTemplate: '/bookings/master',
            controller: MasterBookingsAction::class,
            security: "is_granted('ROLE_USER')",
            name: 'masterBookings',
            read: false,
            deserialize: false,
            normalizationContext: ['groups' => ['booking:read', 'booking:list']],
        ),
        new Get(
            normalizationContext: ['groups' => ['booking:read']],
        ),
        new Post(
            controller: BookingCreateAction::class,
            normalizationContext: ['groups' => ['booking:read']],
        ),
        new Patch(
            normalizationContext: ['groups' => ['booking:read']],
            denormalizationContext: ['groups' => ['booking:write']],
        ),
        new Patch(
            uriTemplate: '/bookings/{id}/confirm',
            controller: BookingConfirmAction::class,
            normalizationContext: ['groups' => ['booking:read']],
            name: 'bookingConfirm',
        ),
        new Patch(
            uriTemplate: '/bookings/{id}/complete',
            controller: BookingCompleteAction::class,
            normalizationContext: ['groups' => ['booking:read']],
            name: 'bookingComplete',
        ),
        new Patch(
            uriTemplate: '/bookings/{id}/cancel',
            controller: BookingCancelAction::class,
            normalizationContext: ['groups' => ['booking:read']],
            denormalizationContext: ['groups' => ['booking:cancel:write']],
            name: 'bookingCancel',
        ),
    ],
    normalizationContext: ['groups' => ['booking:read', 'booking:list']],
    denormalizationContext: ['groups' => ['booking:write']],
)]
#[ApiFilter(SearchFilter::class, properties: ['id' => 'exact', 'status' => 'exact'])]
#[ORM\Entity(repositoryClass: BookingRepository::class)]
class Booking implements
    CreatedAtSettableInterface,
    UpdatedAtSettableInterface,
    DeletedAtSettableInterface
{
    use CreatedAtAccessorsTrait;
    use UpdatedAtAndByAccessorsTrait;
    use DeletedAtAndByAccessorsTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['booking:read', 'booking:list'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $client = null;

    #[ORM\ManyToOne(targetEntity: Master::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Master $master = null;

    #[ORM\ManyToOne(targetEntity: MasterService::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?MasterService $service = null;

    #[ORM\Column(type: 'string', length: 20)]
    #[Groups(['booking:read', 'booking:list'])]
    private string $status = BookingStatus::PENDING;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['booking:read', 'booking:list'])]
    private string $total;

    #[ORM\Column(type: 'date')]
    #[Groups(['booking:read', 'booking:list'])]
    private ?\DateTimeInterface $slotDate = null;

    #[ORM\Column(type: 'string', length: 5)]
    #[Groups(['booking:read', 'booking:list'])]
    private ?string $slotStartTime = null;

    #[ORM\Column(type: 'string', length: 5)]
    #[Groups(['booking:read', 'booking:list'])]
    private ?string $slotEndTime = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['booking:read', 'booking:list'])]
    private ?string $notes = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['booking:read', 'booking:list'])]
    private ?string $cancelledReason = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['booking:read', 'booking:list'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['booking:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getMaster(): ?Master
    {
        return $this->master;
    }

    public function setMaster(?Master $master): self
    {
        $this->master = $master;

        return $this;
    }

    public function getService(): ?MasterService
    {
        return $this->service;
    }

    public function setService(?MasterService $service): self
    {
        $this->service = $service;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getTotal(): string
    {
        return $this->total;
    }

    public function setTotal(string $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getSlotDate(): ?\DateTimeInterface
    {
        return $this->slotDate;
    }

    public function setSlotDate(?\DateTimeInterface $slotDate): self
    {
        $this->slotDate = $slotDate;

        return $this;
    }

    public function getSlotStartTime(): ?string
    {
        return $this->slotStartTime;
    }

    public function setSlotStartTime(string $slotStartTime): self
    {
        $this->slotStartTime = $slotStartTime;

        return $this;
    }

    public function getSlotEndTime(): ?string
    {
        return $this->slotEndTime;
    }

    public function setSlotEndTime(string $slotEndTime): self
    {
        $this->slotEndTime = $slotEndTime;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    public function getCancelledReason(): ?string
    {
        return $this->cancelledReason;
    }

    public function setCancelledReason(?string $cancelledReason): self
    {
        $this->cancelledReason = $cancelledReason;

        return $this;
    }

    #[Groups(['booking:read', 'booking:list'])]
    public function getServiceName(): string
    {
        return $this->service?->getName() ?? '';
    }

    #[Groups(['booking:read', 'booking:list'])]
    public function getServiceDuration(): int
    {
        return $this->service?->getDurationMinutes() ?? 0;
    }

    #[Groups(['booking:read', 'booking:list'])]
    public function getMasterFirstName(): string
    {
        return $this->master?->getFirstName() ?? '';
    }

    #[Groups(['booking:read', 'booking:list'])]
    public function getMasterLastName(): string
    {
        return $this->master?->getLastName() ?? '';
    }

    #[Groups(['booking:read', 'booking:list'])]
    public function getMasterId(): ?int
    {
        return $this->master?->getId();
    }

    #[Groups(['booking:read', 'booking:list'])]
    public function getClientFirstName(): string
    {
        return $this->client?->getFirstName() ?? '';
    }

    #[Groups(['booking:read', 'booking:list'])]
    public function getClientLastName(): string
    {
        return $this->client?->getLastName() ?? '';
    }

    #[Groups(['booking:read', 'booking:list'])]
    public function getClientPhone(): string
    {
        $notes = $this->notes ?? '';
        if (str_contains($notes, ' | ')) {
            return explode(' | ', $notes)[0];
        }
        return '';
    }
}
