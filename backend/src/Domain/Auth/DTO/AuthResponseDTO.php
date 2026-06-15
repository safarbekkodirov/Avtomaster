<?php
// src/Domain/Auth/DTO/AuthResponseDTO.php

namespace App\Domain\Auth\DTO;

final class AuthResponseDTO
{
    public function __construct(
        public readonly string $accessToken,
        public readonly int    $expiresIn,   // секунды
        public readonly array  $user,
    ) {}
}
