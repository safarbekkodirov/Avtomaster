<?php
// src/Domain/Auth/DTO/RegisterRequestDTO.php

namespace App\Domain\Auth\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class RegisterRequestDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        #[Assert\Length(max: 180)]
        public readonly string $email,

        #[Assert\NotBlank]
        #[Assert\Length(min: 8, max: 72)]
        public readonly string $password,

        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 100)]
        public readonly string $firstName,

        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 100)]
        public readonly string $lastName,

        #[Assert\Choice(choices: ['client', 'master'])]
        public readonly string $role = 'client',
    ) {}
}
