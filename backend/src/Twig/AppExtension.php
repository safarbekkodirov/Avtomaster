<?php
// src/Twig/AppExtension.php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\Extension\GlobalsInterface;

final class AppExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly string $frontendUrl,
    ) {}

    public function getGlobals(): array
    {
        return [
            'frontend_url' => $this->frontendUrl,
        ];
    }
}
