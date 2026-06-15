<?php
// src/Domain/Master/Repository/MasterSlotRepository.php

namespace App\Domain\Master\Repository;

use App\Domain\Master\Entity\MasterSlot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;

class MasterSlotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MasterSlot::class);
    }

    /**
     * Доступные слоты мастера за период.
     * Фильтруем по длительности услуги — слот должен вмещать услугу.
     */
    public function findAvailable(int $masterId, \DateTimeImmutable $from, \DateTimeImmutable $to, ?int $durationMinutes = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.master = :master')
            ->andWhere('s.slotDate BETWEEN :from AND :to')
            ->andWhere('s.status = :status')
            ->setParameter('master', $masterId)
            ->setParameter('from',   $from->format('Y-m-d'))
            ->setParameter('to',     $to->format('Y-m-d'))
            ->setParameter('status', MasterSlot::STATUS_AVAILABLE)
            ->orderBy('s.slotDate', 'ASC')
            ->addOrderBy('s.startTime', 'ASC');

        // Фильтрация по длительности — слот должен быть >= длительности услуги
        if ($durationMinutes !== null) {
            $qb->andWhere(
                "TIME_TO_SEC(TIMEDIFF(s.endTime, s.startTime)) / 60 >= :duration"
            )->setParameter('duration', $durationMinutes);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Pessimistic lock для защиты от race condition при бронировании
     */
    public function findWithLock(int $slotId): ?MasterSlot
    {
        return $this->createQueryBuilder('s')
            ->where('s.id = :id')
            ->setParameter('id', $slotId)
            ->getQuery()
            ->setLockMode(LockMode::PESSIMISTIC_WRITE)
            ->getOneOrNullResult();
    }

    /**
     * Генерация слотов из расписания мастера на N дней вперёд.
     * Вызывается из команды/крона.
     *
     * @return MasterSlot[]
     */
    public function generateSlotsFromSchedule(
        int $masterId,
        array $schedules,
        int $slotDurationMinutes,
        int $daysAhead = 30
    ): array {
        $slots = [];
        $today = new \DateTimeImmutable('today');

        for ($i = 0; $i < $daysAhead; $i++) {
            $date      = $today->modify("+{$i} days");
            $dayOfWeek = (int) $date->format('N') - 1; // 0=пн, 6=вс

            foreach ($schedules as $schedule) {
                if ($schedule->getDayOfWeek() !== $dayOfWeek || !$schedule->isActive()) {
                    continue;
                }

                $current = \DateTime::createFromFormat('H:i:s', $schedule->getStartTime()->format('H:i:s'));
                $end     = \DateTime::createFromFormat('H:i:s', $schedule->getEndTime()->format('H:i:s'));

                while ($current < $end) {
                    $slotEnd = clone $current;
                    $slotEnd->modify("+{$slotDurationMinutes} minutes");

                    if ($slotEnd > $end) {
                        break;
                    }

                    $slot = new MasterSlot(
                        master:    $this->getEntityManager()->getReference(\App\Domain\Master\Entity\Master::class, $masterId),
                        slotDate:  $date,
                        startTime: \DateTimeImmutable::createFromMutable($current),
                        endTime:   \DateTimeImmutable::createFromMutable($slotEnd),
                    );

                    $slots[] = $slot;
                    $current->modify("+{$slotDurationMinutes} minutes");
                }
            }
        }

        return $slots;
    }
}
