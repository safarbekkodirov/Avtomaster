<?php
// src/Domain/Auth/Repository/RefreshTokenRepository.php

namespace App\Domain\Auth\Repository;

use App\Domain\Auth\Entity\RefreshToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RefreshTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefreshToken::class);
    }

    public function findByTokenHash(string $hash): ?RefreshToken
    {
        return $this->findOneBy(['tokenHash' => $hash]);
    }

    /**
     * Инвалидация всей семьи токенов — вызывается при reuse detection
     */
    public function invalidateFamily(string $familyId): void
    {
        $this->createQueryBuilder('t')
            ->update()
            ->set('t.isUsed', ':used')
            ->where('t.familyId = :family')
            ->setParameter('used',   true)
            ->setParameter('family', $familyId)
            ->getQuery()
            ->execute();
    }

    /**
     * Инвалидация всех токенов пользователя (logout everywhere)
     */
    public function invalidateAllForUser(int $userId): void
    {
        $this->createQueryBuilder('t')
            ->update()
            ->set('t.isUsed', ':used')
            ->where('t.user = :userId')
            ->setParameter('used',   true)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->execute();
    }

    /**
     * Чистка протухших токенов — запускать крон раз в сутки
     */
    public function deleteExpired(): int
    {
        return $this->createQueryBuilder('t')
            ->delete()
            ->where('t.expiresAt < :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->execute();
    }
}
