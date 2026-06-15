<?php
// src/Domain/Master/Service/SlotService.php

namespace App\Domain\Master\Service;

use App\Domain\Master\DTO\SlotSearchRequestDTO;
use App\Domain\Master\Entity\MasterSlot;
use App\Domain\Master\Repository\MasterRepository;
use App\Domain\Master\Repository\MasterSlotRepository;

final class SlotService
{
    public function __construct(
        private readonly MasterSlotRepository $slotRepository,
        private readonly MasterRepository     $masterRepository,
    ) {}

    /**
     * @return MasterSlot[]
     */
    public function getAvailableSlots(int $masterId, SlotSearchRequestDTO $dto): array
    {
        $master = $this->masterRepository->find($masterId);
        if ($master === null || !$master->isActive()) {
            throw new \DomainException('Мастер не найден');
        }

        $from     = new \DateTimeImmutable($dto->dateFrom ?? 'today');
        $to       = new \DateTimeImmutable($dto->dateTo ?? '+7 days');
        $duration = null;

        // Если указана услуга — фильтруем слоты по её длительности
        if ($dto->serviceId !== null) {
            $service  = $master->getServices()->filter(
                fn($s) => $s->getId() === $dto->serviceId && $s->isActive()
            )->first();

            if ($service === false) {
                throw new \DomainException('Услуга не найдена у данного мастера');
            }

            $duration = $service->getDuration();
        }

        return $this->slotRepository->findAvailable($masterId, $from, $to, $duration);
    }
}
