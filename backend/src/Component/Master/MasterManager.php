<?php

declare(strict_types=1);

namespace App\Component\Master;

use App\Component\Core\AbstractManager;
use App\Entity\Master;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class MasterManager
 *
 * @method save(Master $entity, bool $needToFlush = false): void
 */
class MasterManager extends AbstractManager
{
    public function __construct(
        EntityManagerInterface $entityManager,
        \App\Component\User\CurrentUser $currentUser,
    ) {
        parent::__construct($entityManager, $currentUser);
    }
}
