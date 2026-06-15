<?php
// src/Domain/Admin/Service/AuditService.php

namespace App\Domain\Admin\Service;

use App\Domain\Admin\Entity\AdminAuditLog;
use App\Domain\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class AuditService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RequestStack           $requestStack,
    ) {}

    public function log(
        User   $admin,
        object $entity,
        string $action,
        ?array $changes = null,
    ): void {
        $log = new AdminAuditLog(
            admin:       $admin,
            entityClass: $entity::class,
            entityId:    method_exists($entity, 'getId') ? $entity->getId() : 0,
            action:      $action,
            changes:     $changes,
            ipAddress:   $this->requestStack->getCurrentRequest()?->getClientIp(),
        );

        $this->em->persist($log);
        // Не делаем flush — вызывающий код управляет транзакцией
    }

    /**
     * Утилита для получения diff между двумя состояниями объекта
     *
     * @param array $before ['field' => 'oldValue']
     * @param array $after  ['field' => 'newValue']
     */
    public function diff(array $before, array $after): array
    {
        $changes = [];
        foreach ($after as $field => $newValue) {
            $oldValue = $before[$field] ?? null;
            if ($oldValue !== $newValue) {
                $changes[$field] = ['before' => $oldValue, 'after' => $newValue];
            }
        }
        return $changes;
    }
}
