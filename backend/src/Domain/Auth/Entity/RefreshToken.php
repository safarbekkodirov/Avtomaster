<?php
// src/Domain/Auth/Entity/RefreshToken.php

namespace App\Domain\Auth\Entity;

use App\Domain\User\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'refresh_tokens')]
class RefreshToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    // Хранится хэш, не сам токен
    #[ORM\Column(type: 'string', length: 64, unique: true)]
    private string $tokenHash;

    // UUID семьи — объединяет цепочку rotation для одного устройства
    #[ORM\Column(type: 'string', length: 36)]
    private string $familyId;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isUsed = false;

    #[ORM\Column(type: 'string', length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $expiresAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $usedAt = null;

    public function __construct(
        User   $user,
        string $tokenHash,
        string $familyId,
        \DateTimeImmutable $expiresAt,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ) {
        $this->user      = $user;
        $this->tokenHash = $tokenHash;
        $this->familyId  = $familyId;
        $this->expiresAt = $expiresAt;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function markAsUsed(): void
    {
        $this->isUsed = true;
        $this->usedAt = new \DateTimeImmutable();
    }

    public function isExpired(): bool
    {
        return $this->expiresAt < new \DateTimeImmutable();
    }

    public function isValid(): bool
    {
        return !$this->isUsed && !$this->isExpired();
    }

    public function getUser(): User       { return $this->user; }
    public function getTokenHash(): string { return $this->tokenHash; }
    public function getFamilyId(): string  { return $this->familyId; }
    public function isUsed(): bool         { return $this->isUsed; }
    public function getExpiresAt(): \DateTimeImmutable { return $this->expiresAt; }
}
