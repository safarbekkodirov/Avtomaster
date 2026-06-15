<?php
// src/Domain/Booking/Entity/Booking.php

namespace App\Domain\Booking\Entity;

use App\Domain\Master\Entity\Master;
use App\Domain\Master\Entity\MasterService;
use App\Domain\Master\Entity\MasterSlot;
use App\Domain\User\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'bookings')]
#[ORM\Index(columns: ['client_id'], name: 'idx_client')]
#[ORM\Index(columns: ['master_id', 'status', 'created_at'], name: 'idx_master_status_date')]
class Booking
{
    public const STATUS_PENDING   = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED  = 'refunded';

    // Допустимые переходы состояний
    private const TRANSITIONS = [
        self::STATUS_PENDING   => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED],
        self::STATUS_CONFIRMED => [self::STATUS_COMPLETED, self::STATUS_CANCELLED],
        self::STATUS_COMPLETED => [self::STATUS_REFUNDED],
        self::STATUS_CANCELLED => [],
        self::STATUS_REFUNDED  => [],
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private User $client;

    #[ORM\ManyToOne(targetEntity: Master::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Master $master;

    #[ORM\OneToOne(targetEntity: MasterSlot::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT', unique: true)]
    private MasterSlot $slot;

    #[ORM\ManyToOne(targetEntity: MasterService::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private MasterService $service;

    #[ORM\Column(type: 'string', length: 20)]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $total;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $cancelledReason = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $cancelledAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $confirmedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        User          $client,
        Master        $master,
        MasterSlot    $slot,
        MasterService $service,
        string        $total,
        ?string       $notes = null,
    ) {
        $this->client    = $client;
        $this->master    = $master;
        $this->slot      = $slot;
        $this->service   = $service;
        $this->total     = $total;
        $this->notes     = $notes;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    // --- State machine ---

    public function confirm(): void
    {
        $this->transition(self::STATUS_CONFIRMED);
        $this->confirmedAt = new \DateTimeImmutable();
    }

    public function complete(): void
    {
        $this->transition(self::STATUS_COMPLETED);
        $this->completedAt = new \DateTimeImmutable();
    }

    public function cancel(string $reason = ''): void
    {
        $this->transition(self::STATUS_CANCELLED);
        $this->cancelledReason = $reason ?: null;
        $this->cancelledAt     = new \DateTimeImmutable();
    }

    public function refund(): void
    {
        $this->transition(self::STATUS_REFUNDED);
    }

    private function transition(string $newStatus): void
    {
        if (!in_array($newStatus, self::TRANSITIONS[$this->status], true)) {
            throw new \DomainException(
                sprintf('Переход из "%s" в "%s" недопустим', $this->status, $newStatus)
            );
        }

        $this->status    = $newStatus;
        $this->updatedAt = new \DateTimeImmutable();
    }

    // --- Бизнес-правила ---

    /**
     * Клиент может отменить до начала слота.
     * Мастер и админ — в любое время (пока не завершено).
     */
    public function canBeCancelledBy(User $actor): bool
    {
        if (!in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED], true)) {
            return false;
        }

        if ($actor->hasRole('ROLE_ADMIN')) {
            return true;
        }

        if ($this->master->getUser()->getId() === $actor->getId()) {
            return true;
        }

        if ($this->client->getId() === $actor->getId()) {
            // Клиент может отменить только до начала слота
            return $this->slot->getSlotDate() > new \DateTimeImmutable('today');
        }

        return false;
    }

    public function canBeConfirmedBy(User $actor): bool
    {
        return $this->status === self::STATUS_PENDING
            && $this->master->getUser()->getId() === $actor->getId();
    }

    public function canBeCompletedBy(User $actor): bool
    {
        return $this->status === self::STATUS_CONFIRMED
            && $this->master->getUser()->getId() === $actor->getId();
    }

    public function isOwnedByClient(User $client): bool
    {
        return $this->client->getId() === $client->getId();
    }

    // --- Getters ---

    public function getId(): int                       { return $this->id; }
    public function getClient(): User                  { return $this->client; }
    public function getMaster(): Master                { return $this->master; }
    public function getSlot(): MasterSlot              { return $this->slot; }
    public function getService(): MasterService        { return $this->service; }
    public function getStatus(): string                { return $this->status; }
    public function getTotal(): string                 { return $this->total; }
    public function getNotes(): ?string                { return $this->notes; }
    public function getCancelledReason(): ?string      { return $this->cancelledReason; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
