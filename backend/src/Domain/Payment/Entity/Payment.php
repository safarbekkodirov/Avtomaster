<?php
// src/Domain/Payment/Entity/Payment.php

namespace App\Domain\Payment\Entity;

use App\Domain\Booking\Entity\Booking;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'payments')]
class Payment
{
    public const STATUS_PENDING  = 'pending';
    public const STATUS_PAID     = 'paid';
    public const STATUS_FAILED   = 'failed';
    public const STATUS_REFUNDED = 'refunded';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private int $id;

    #[ORM\OneToOne(targetEntity: Booking::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT', unique: true)]
    private Booking $booking;

    #[ORM\Column(type: 'string', length: 50)]
    private string $gateway = 'stripe';

    // Stripe PaymentIntent ID или Checkout Session ID
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $gatewayPaymentId = null;

    // Stripe Checkout Session ID — для получения checkout URL
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $gatewaySessionId = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $amount;

    #[ORM\Column(type: 'string', length: 3, options: ['default' => 'RUB'])]
    private string $currency;

    #[ORM\Column(type: 'string', length: 20)]
    private string $status = self::STATUS_PENDING;

    // URL для редиректа на Stripe Checkout
    #[ORM\Column(type: 'string', length: 512, nullable: true)]
    private ?string $checkoutUrl = null;

    // Raw payload последнего события от Stripe
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $payload = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $gatewayRefundId = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(Booking $booking, string $amount, string $currency)
    {
        $this->booking   = $booking;
        $this->amount    = $amount;
        $this->currency  = $currency;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function markAsPaid(string $gatewayPaymentId, array $payload): void
    {
        $this->gatewayPaymentId = $gatewayPaymentId;
        $this->status           = self::STATUS_PAID;
        $this->payload          = $payload;
        $this->updatedAt        = new \DateTimeImmutable();
    }

    public function markAsFailed(array $payload): void
    {
        $this->status    = self::STATUS_FAILED;
        $this->payload   = $payload;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function markAsRefunded(string $refundId): void
    {
        $this->gatewayRefundId = $refundId;
        $this->status          = self::STATUS_REFUNDED;
        $this->updatedAt       = new \DateTimeImmutable();
    }

    public function setGatewaySession(string $sessionId, string $checkoutUrl): void
    {
        $this->gatewaySessionId = $sessionId;
        $this->checkoutUrl      = $checkoutUrl;
        $this->updatedAt        = new \DateTimeImmutable();
    }

    public function isPaid(): bool     { return $this->status === self::STATUS_PAID; }
    public function isPending(): bool  { return $this->status === self::STATUS_PENDING; }

    public function getId(): int                          { return $this->id; }
    public function getBooking(): Booking                 { return $this->booking; }
    public function getGatewayPaymentId(): ?string        { return $this->gatewayPaymentId; }
    public function getGatewaySessionId(): ?string        { return $this->gatewaySessionId; }
    public function getAmount(): string                   { return $this->amount; }
    public function getCurrency(): string                 { return $this->currency; }
    public function getStatus(): string                   { return $this->status; }
    public function getCheckoutUrl(): ?string             { return $this->checkoutUrl; }
    public function getCreatedAt(): \DateTimeImmutable    { return $this->createdAt; }
}
