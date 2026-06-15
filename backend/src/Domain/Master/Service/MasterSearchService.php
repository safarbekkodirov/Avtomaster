<?php
// src/Domain/Master/Service/MasterSearchService.php

namespace App\Domain\Master\Service;

use App\Domain\Master\DTO\MasterSearchRequestDTO;
use App\Domain\Master\DTO\SearchResultDTO;
use App\Domain\Master\Repository\MasterSearchRepositoryInterface;

final class MasterSearchService
{
    public function __construct(
        private readonly MasterSearchRepositoryInterface $repository,
    ) {}

    public function search(MasterSearchRequestDTO $dto): SearchResultDTO
    {
        // Валидация — если переданы координаты, должны быть оба
        if (($dto->lat === null) !== ($dto->lng === null)) {
            throw new \InvalidArgumentException('lat и lng должны передаваться вместе');
        }

        return $this->repository->search($dto);
    }
}
