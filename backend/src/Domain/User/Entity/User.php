<?php
// src/Domain/User/Entity/User.php

namespace App\Domain\User\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private int $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private string $email;

    #[ORM\Column(type: 'json')]
    private array $roles;

    #[ORM\Column(type: 'string')]
    private string $password;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToOne(targetEntity: UserProfile::class, mappedBy: 'user', cascade: ['persist'])]
    private ?UserProfile $profile = null;

    public function __construct(
        string $email,
        array  $roles = ['ROLE_CLIENT'],
    ) {
        $this->email     = $email;
        $this->roles     = $roles;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): int { return $this->id; }

    public function getUserIdentifier(): string { return $this->email; }

    public function getEmail(): string { return $this->email; }

    public function getRoles(): array { return $this->roles; }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles, true);
    }

    public function getPassword(): string { return $this->password; }

    public function setPasswordHash(string $password): void
    {
        $this->password = $password;
    }

    public function eraseCredentials(): void {}

    public function isActive(): bool { return $this->isActive; }

    public function getProfile(): ?UserProfile { return $this->profile; }

    public function setProfile(UserProfile $profile): void
    {
        $this->profile = $profile;
    }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
