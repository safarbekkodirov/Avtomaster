<?php
// src/Domain/Booking/Repository/BookingRepository.php

namespace App\Domain\Booking\Repository;

use App\Domain\Booking\Entity\Booking;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;

class BookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    /**
     * Список бронирований клиента с пагинацией
     *
     * @return array{items: Booking[], total: int}
     */
    public function findByClient(int $clientId, int $page, int $perPage): array
    {
        $qb = $this->createQueryBuilder('b')
            ->innerJoin('b.slot', 's')
            ->innerJoin('b.service', 'srv')
            ->innerJoin('b.master', 'm')
            ->innerJoin('m.user', 'mu')
            ->innerJoin('mu.profile', 'mp')
            ->addSelect('s', 'srv', 'm', 'mu', 'mp')
            ->where('b.client = :clientId')
            ->setParameter('clientId', $clientId)
            ->orderBy('b.createdAt', 'DESC');

        $total = (clone $qb)
            ->select('COUNT(b.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $items = $qb
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();

        return ['items' => $items, 'total' => (int) $total];
    }

    /**
     * Список бронирований мастера с фильтром по статусу
     *
     * @return array{items: Booking[], total: int}
     */
    public function findByMaster(
        int     $masterId,
        int     $page,
        int     $perPage,
        ?string $status = null
    ): array {
        $qb = $this->createQueryBuilder('b')
            ->innerJoin('b.slot', 's')
            ->innerJoin('b.service', 'srv')
            ->innerJoin('b.client', 'c')
            ->innerJoin('c.profile', 'cp')
            ->addSelect('s', 'srv', 'c', 'cp')
            ->where('b.master = :masterId')
            ->setParameter('masterId', $masterId)
            ->orderBy('s.slotDate', 'ASC')
            ->addOrderBy('s.startTime', 'ASC');

        if ($status !== null) {
            $qb->andWhere('b.status = :status')
                ->setParameter('status', $status);
        }

        $total = (clone $qb)
            ->select('COUNT(b.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $items = $qb
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();

        return ['items' => $items, 'total' => (int) $total];
    }

    /**
     * Загрузка с lock — используется только внутри транзакции BookingService
     */
    public function findWithLock(int $id): ?Booking
    {
        return $this->createQueryBuilder('b')
            ->innerJoin('b.slot', 's')
            ->addSelect('s')
            ->where('b.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->setLockMode(LockMode::PESSIMISTIC_WRITE)
            ->getOneOrNullResult();
    }
}
