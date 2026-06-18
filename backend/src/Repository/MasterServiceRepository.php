<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MasterService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MasterService|null find($id, $lockMode = null, $lockVersion = null)
 * @method MasterService|null findOneBy(array $criteria, array $orderBy = null)
 * @method MasterService[]    findAll()
 * @method MasterService[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MasterServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MasterService::class);
    }
}
