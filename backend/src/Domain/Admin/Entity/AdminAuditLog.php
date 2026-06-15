<?php
// src/Domain/Admin/Entity/AdminAuditLog.php

namespace App\Domain\Admin\Entity;

use App\Domain\User\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'admin_audit_log')]
class AdminAuditLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private User $admin;

    #[ORM\Column(type: 'string', length: 255)]
    private string $entityClass;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private int $entityId;

    #[ORM\Column(type: 'string', length: 50)]
    private string $action;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $changes = null;

    #[ORM\Column(type: 'string', length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        User    $admin,
        string  $entityClass,
        int     $entityId,
        string  $action,
        ?array  $changes   = null,
        ?string $ipAddress = null,
    ) {
        $this->admin       = $admin;
        $this->entityClass = $entityClass;
        $this->entityId    = $entityId;
        $this->action      = $action;
        $this->changes     = $changes;
        $this->ipAddress   = $ipAddress;
        $this->createdAt   = new \DateTimeImmutable();
    }

    public function getId(): int                          { return $this->id; }
    public function getAdmin(): User                      { return $this->admin; }
    public function getEntityClass(): string              { return $this->entityClass; }
    public function getEntityId(): int                    { return $this->entityId; }
    public function getAction(): string                   { return $this->action; }
    public function getChanges(): ?array                  { return $this->changes; }
    public function getCreatedAt(): \DateTimeImmutable    { return $this->createdAt; }
}
