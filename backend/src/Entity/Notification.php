<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interfaces\CreatedAtSettableInterface;
use App\Entity\Interfaces\UpdatedAtSettableInterface;
use App\Entity\Interfaces\DeletedAtSettableInterface;
use App\Entity\Traits\CreatedAtAccessorsTrait;
use App\Entity\Traits\UpdatedAtAndByAccessorsTrait;
use App\Entity\Traits\DeletedAtAndByAccessorsTrait;
use App\Repository\NotificationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
class Notification implements
    CreatedAtSettableInterface,
    UpdatedAtSettableInterface,
    DeletedAtSettableInterface
{
    use CreatedAtAccessorsTrait;
    use UpdatedAtAndByAccessorsTrait;
    use DeletedAtAndByAccessorsTrait;

    public const TYPE_BOOKING_CREATED   = 'booking_created';
    public const TYPE_BOOKING_CONFIRMED = 'booking_confirmed';
    public const TYPE_BOOKING_COMPLETED = 'booking_completed';
    public const TYPE_BOOKING_CANCELLED = 'booking_cancelled';
    public const TYPE_PAYMENT_RECEIVED  = 'payment_received';
    public const TYPE_REVIEW_RECEIVED   = 'review_received';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['notification:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'string', length: 50)]
    #[Groups(['notification:read'])]
    private string $type;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['notification:read'])]
    private string $title;

    #[ORM\Column(type: 'text')]
    #[Groups(['notification:read'])]
    private string $body;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $relatedId = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $relatedType = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(['notification:read'])]
    private bool $isRead = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['notification:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getRelatedId(): ?int
    {
        return $this->relatedId;
    }

    public function setRelatedId(?int $relatedId): self
    {
        $this->relatedId = $relatedId;

        return $this;
    }

    public function getRelatedType(): ?string
    {
        return $this->relatedType;
    }

    public function setRelatedType(?string $relatedType): self
    {
        $this->relatedType = $relatedType;

        return $this;
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): self
    {
        $this->isRead = $isRead;

        return $this;
    }
}
