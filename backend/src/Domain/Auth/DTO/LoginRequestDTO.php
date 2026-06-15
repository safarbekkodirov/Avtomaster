<?php
// src/Domain/Auth/DTO/LoginRequestDTO.php

namespace App\Domain\Auth\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class LoginRequestDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public readonly string $email,

        #[Assert\NotBlank]
        public readonly string $password,
    ) {}
}
