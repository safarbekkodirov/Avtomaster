<?php
// src/Domain/Master/Entity/MasterSlot.php

namespace App\Domain\Master\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'master_slots')]
class MasterSlot
{
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_BOOKED    = 'booked';
    public const STATUS_BLOCKED   = 'blocked';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Master::class, inversedBy: 'slots')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Master $master;

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $slotDate;

    #[ORM\Column(type: 'time_immutable')]
    private \DateTimeImmutable $startTime;

    #[ORM\Column(type: 'time_immutable')]
    private \DateTimeImmutable $endTime;

    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'available'])]
    private string $status = self::STATUS_AVAILABLE;

    public function __construct(
        Master $master,
        \DateTimeImmutable $slotDate,
        \DateTimeImmutable $startTime,
        \DateTimeImmutable $endTime,
    ) {
        $this->master    = $master;
        $this->slotDate  = $slotDate;
        $this->startTime = $startTime;
        $this->endTime   = $endTime;
    }

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    public function book(): void
    {
        if (!$this->isAvailable()) {
            throw new \DomainException('Слот недоступен для бронирования');
        }
        $this->status = self::STATUS_BOOKED;
    }

    public function release(): void
    {
        if ($this->status !== self::STATUS_BOOKED) {
            throw new \DomainException('Нельзя освободить незабронированный слот');
        }
        $this->status = self::STATUS_AVAILABLE;
    }

    public function getId(): int                          { return $this->id; }
    public function getMaster(): Master                   { return $this->master; }
    public function getSlotDate(): \DateTimeImmutable     { return $this->slotDate; }
    public function getStartTime(): \DateTimeImmutable    { return $this->startTime; }
    public function getEndTime(): \DateTimeImmutable      { return $this->endTime; }
    public function getStatus(): string                   { return $this->status; }
}
