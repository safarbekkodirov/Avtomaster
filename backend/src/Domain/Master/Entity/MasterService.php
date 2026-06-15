<?php
// src/Domain/Master/Entity/MasterService.php

namespace App\Domain\Master\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'master_services')]
class MasterService
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Master::class, inversedBy: 'services')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Master $master;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $price;

    #[ORM\Column(type: 'integer')]
    private int $duration; // minutes

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $categoryId = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        Master $master,
        string $name,
        string $price,
        int    $duration,
    ) {
        $this->master    = $master;
        $this->name      = $name;
        $this->price     = $price;
        $this->duration  = $duration;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function setCategoryId(?int $id): void { $this->categoryId = $id; }

    public function getId(): int          { return $this->id; }
    public function getMaster(): Master   { return $this->master; }
    public function getName(): string     { return $this->name; }
    public function getPrice(): string    { return $this->price; }
    public function getDuration(): int    { return $this->duration; }
    public function getCategoryId(): ?int { return $this->categoryId; }
    public function isActive(): bool      { return $this->isActive; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
