<?php
// src/Domain/Payment/Repository/PaymentRepository.php

namespace App\Domain\Payment\Repository;

use App\Domain\Payment\Entity\Payment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    public function findByBookingId(int $bookingId): ?Payment
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.booking', 'b')
            ->where('b.id = :bookingId')
            ->setParameter('bookingId', $bookingId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByGatewaySessionId(string $sessionId): ?Payment
    {
        return $this->findOneBy(['gatewaySessionId' => $sessionId]);
    }
}
