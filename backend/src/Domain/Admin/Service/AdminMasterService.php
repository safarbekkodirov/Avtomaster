<?php
// src/Domain/Admin/Service/AdminMasterService.php

namespace App\Domain\Admin\Service;

use App\Domain\Master\Entity\Master;
use App\Domain\Master\Repository\MasterRepository;
use App\Domain\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class AdminMasterService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MasterRepository       $masterRepository,
        private readonly AuditService           $audit,
    ) {}

    public function verify(int $masterId, User $admin): void
    {
        $master = $this->getMasterOrFail($masterId);

        $before = ['isVerified' => $master->isVerified()];
        $master->verify();
        $after  = ['isVerified' => $master->isVerified()];

        $this->audit->log($admin, $master, 'verify', $this->audit->diff($before, $after));
        $this->em->flush();
    }

    public function block(int $masterId, User $admin): void
    {
        $master = $this->getMasterOrFail($masterId);

        $before = ['isActive' => $master->isActive()];
        $master->deactivate();
        $after  = ['isActive' => $master->isActive()];

        $this->audit->log($admin, $master, 'block', $this->audit->diff($before, $after));
        $this->em->flush();
    }

    public function unblock(int $masterId, User $admin): void
    {
        $master = $this->getMasterOrFail($masterId);

        $before = ['isActive' => $master->isActive()];
        $master->activate();
        $after  = ['isActive' => $master->isActive()];

        $this->audit->log($admin, $master, 'unblock', $this->audit->diff($before, $after));
        $this->em->flush();
    }

    private function getMasterOrFail(int $id): Master
    {
        $master = $this->masterRepository->find($id);
        if ($master === null) {
            throw new \DomainException('Мастер не найден');
        }
        return $master;
    }
}
