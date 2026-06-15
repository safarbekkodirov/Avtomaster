<?php
// src/Domain/Admin/Service/AdminStatsService.php

namespace App\Domain\Admin\Service;

use Doctrine\ORM\EntityManagerInterface;

final class AdminStatsService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    public function getDashboardStats(): array
    {
        $conn  = $this->em->getConnection();
        $today = (new \DateTimeImmutable('today'))->format('Y-m-d');

        return [
            'bookingsToday'  => (int) $conn->fetchOne(
                "SELECT COUNT(*) FROM bookings WHERE DATE(created_at) = ?", [$today]
            ),
            'revenueToday'   => (float) $conn->fetchOne(
                "SELECT COALESCE(SUM(p.amount), 0)
                 FROM payments p
                 INNER JOIN bookings b ON b.id = p.booking_id
                 WHERE p.status = 'paid' AND DATE(p.created_at) = ?",
                [$today]
            ),
            'pendingMasters' => (int) $conn->fetchOne(
                "SELECT COUNT(*) FROM masters WHERE is_verified = 0 AND is_active = 1"
            ),
            'newReports'     => 0, // расширить при добавлении модуля жалоб
        ];
    }
}
