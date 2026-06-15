<?php
// src/Domain/Master/Repository/MasterSearchRepositoryInterface.php

namespace App\Domain\Master\Repository;

use App\Domain\Master\DTO\MasterSearchRequestDTO;
use App\Domain\Master\DTO\SearchResultDTO;

interface MasterSearchRepositoryInterface
{
    public function search(MasterSearchRequestDTO $dto): SearchResultDTO;
}
