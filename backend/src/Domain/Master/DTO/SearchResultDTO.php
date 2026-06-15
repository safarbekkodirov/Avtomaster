<?php
// src/Domain/Master/DTO/SearchResultDTO.php

namespace App\Domain\Master\DTO;

final class SearchResultDTO
{
    public function __construct(
        /** @var MasterResponseDTO[] */
        public readonly array $items,
        public readonly int   $total,
        public readonly int   $page,
        public readonly int   $perPage,
    ) {}
}
