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

    public function search(
        ?string $regionName = null,
        ?string $categorySlug = null,
        ?float $minRating = null,
        string $sortBy = 'rating',
        int $page = 1,
        int $perPage = 20,
    ): array {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.services', 's')
            ->addSelect('s')
            ->leftJoin('s.category', 'sc')
            ->addSelect('sc');

        if ($regionName) {
            $qb->andWhere('m.regionName LIKE :region')
                ->setParameter('region', '%' . $regionName . '%');
        }

        if ($categorySlug) {
            $qb->andWhere('sc.slug = :categorySlug')
                ->setParameter('categorySlug', $categorySlug);
        }

        if ($minRating !== null) {
            $qb->andWhere('m.rating >= :minRating')
                ->setParameter('minRating', $minRating);
        }

        $qb->andWhere('m.deletedAt IS NULL');

        switch ($sortBy) {
            case 'price':
                $qb->orderBy('s.price', 'ASC');
                break;
            case 'distance':
                $qb->orderBy('m.rating', 'DESC');
                break;
            default:
                $qb->orderBy('m.rating', 'DESC');
        }

        $totalQb = clone $qb;
        $totalQb->select('COUNT(DISTINCT m.id)');
        $total = (int) $totalQb->getQuery()->getSingleScalarResult();

        $totalPages = max(1, (int) ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $qb->setMaxResults($perPage)
            ->setFirstResult($offset);

        $data = $qb->getQuery()->getResult();

        return [
            'data' => $data,
            'pagination' => [
                'page'       => $page,
                'perPage'    => $perPage,
                'total'      => $total,
                'totalPages' => $totalPages,
            ],
        ];
    }
}
