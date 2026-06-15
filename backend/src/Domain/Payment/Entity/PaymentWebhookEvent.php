<?php
// src/Domain/Payment/Entity/PaymentWebhookEvent.php

namespace App\Domain\Payment\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'payment_webhook_events')]
class PaymentWebhookEvent
{
    public const STATUS_PENDING   = 'pending';
    public const STATUS_PROCESSED = 'processed';
    public const STATUS_FAILED    = 'failed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private int $id;

    // Stripe event ID — гарантирует идемпотентность
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $gatewayEventId;

    #[ORM\Column(type: 'string', length: 100)]
    private string $type;

    #[ORM\Column(type: 'json')]
    private array $payload;

    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'pending'])]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(type: 'smallint', options: ['default' => 0])]
    private int $attempts = 0;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $processedAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $gatewayEventId, string $type, array $payload)
    {
        $this->gatewayEventId = $gatewayEventId;
        $this->type           = $type;
        $this->payload        = $payload;
        $this->createdAt      = new \DateTimeImmutable();
    }

    public function markProcessed(): void
    {
        $this->status      = self::STATUS_PROCESSED;
        $this->processedAt = new \DateTimeImmutable();
    }

    public function markFailed(): void
    {
        $this->status   = self::STATUS_FAILED;
        $this->attempts++;
    }

    public function incrementAttempts(): void { $this->attempts++; }

    public function getId(): int                          { return $this->id; }
    public function getGatewayEventId(): string { return $this->gatewayEventId; }
    public function getType(): string           { return $this->type; }
    public function getPayload(): array         { return $this->payload; }
    public function getStatus(): string         { return $this->status; }
    public function getAttempts(): int          { return $this->attempts; }
}
