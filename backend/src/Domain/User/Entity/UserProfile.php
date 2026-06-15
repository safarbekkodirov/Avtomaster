<?php
// src/Domain/User/Entity/UserProfile.php

namespace App\Domain\User\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'user_profiles')]
class UserProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private int $id;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'profile')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'string', length: 100)]
    private string $firstName;

    #[ORM\Column(type: 'string', length: 100)]
    private string $lastName;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $avatar = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        User   $user,
        string $firstName,
        string $lastName,
    ) {
        $this->user      = $user;
        $this->firstName = $firstName;
        $this->lastName  = $lastName;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): int           { return $this->id; }
    public function getUser(): User        { return $this->user; }
    public function getFirstName(): string { return $this->firstName; }
    public function getLastName(): string  { return $this->lastName; }
    public function getAvatar(): ?string   { return $this->avatar; }
    public function getPhone(): ?string    { return $this->phone; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function setFirstName(string $firstName): void { $this->firstName = $firstName; }
    public function setLastName(string $lastName): void   { $this->lastName = $lastName; }
    public function setAvatar(?string $avatar): void      { $this->avatar = $avatar; }
    public function setPhone(?string $phone): void        { $this->phone = $phone; }
}
