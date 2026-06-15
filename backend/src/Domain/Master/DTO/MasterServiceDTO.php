<?php
// src/Domain/Master/DTO/MasterServiceDTO.php

namespace App\Domain\Master\DTO;

final class MasterServiceDTO
{
    public function __construct(
        public readonly int     $id,
        public readonly string  $name,
        public readonly float   $price,
        public readonly int     $durationMinutes,
        public readonly string  $categoryName,
    ) {}
}
