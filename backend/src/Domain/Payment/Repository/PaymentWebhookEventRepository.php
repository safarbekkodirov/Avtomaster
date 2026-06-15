<?php
// src/Domain/Payment/Repository/PaymentWebhookEventRepository.php

namespace App\Domain\Payment\Repository;

use App\Domain\Payment\Entity\PaymentWebhookEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PaymentWebhookEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentWebhookEvent::class);
    }

    public function findByGatewayEventId(string $eventId): ?PaymentWebhookEvent
    {
        return $this->findOneBy(['gatewayEventId' => $eventId]);
    }

    /**
     * Повторная обработка зависших событий — запускать крон каждые 5 мин
     *
     * @return PaymentWebhookEvent[]
     */
    public function findFailedForRetry(int $maxAttempts = 3): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.status = :status')
            ->andWhere('e.attempts < :maxAttempts')
            ->setParameter('status',      PaymentWebhookEvent::STATUS_FAILED)
            ->setParameter('maxAttempts', $maxAttempts)
            ->orderBy('e.createdAt', 'ASC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();
    }
}
