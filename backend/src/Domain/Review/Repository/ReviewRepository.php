<?php
// src/Domain/Review/Repository/ReviewRepository.php

namespace App\Domain\Review\Repository;

use App\Domain\Review\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function findByBookingId(int $bookingId): ?Review
    {
        return $this->createQueryBuilder('r')
            ->innerJoin('r.booking', 'b')
            ->where('b.id = :bookingId')
            ->setParameter('bookingId', $bookingId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Публичные отзывы мастера с пагинацией
     *
     * @return array{items: Review[], total: int}
     */
    public function findVisibleByMaster(int $masterId, int $page, int $perPage): array
    {
        $qb = $this->createQueryBuilder('r')
            ->innerJoin('r.client', 'c')
            ->leftJoin('c.profile', 'cp')
            ->addSelect('c', 'cp')
            ->where('r.master = :masterId')
            ->andWhere('r.isVisible = true')
            ->setParameter('masterId', $masterId)
            ->orderBy('r.createdAt', 'DESC');

        $total = (clone $qb)
            ->select('COUNT(r.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $items = $qb
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();

        return ['items' => $items, 'total' => (int) $total];
    }
}
