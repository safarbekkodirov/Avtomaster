<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Master;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Master|null find($id, $lockMode = null, $lockVersion = null)
 * @method Master|null findOneBy(array $criteria, array $orderBy = null)
 * @method Master[]    findAll()
 * @method Master[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MasterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Master::class);
    }

    public function findOneByUserId(int $userId): ?Master
    {
        return $this->findOneBy(['user' => $userId]);
    }

    public function searchByRegion(?string $regionName): array
    {
        $qb = $this->createQueryBuilder('m');

        if ($regionName) {
            $qb->andWhere('m.regionName LIKE :region')
                ->setParameter('region', '%' . $regionName . '%');
        }

        return $qb->getQuery()->getResult();
    }
}
