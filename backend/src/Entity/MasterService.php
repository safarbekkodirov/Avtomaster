<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['master_service:read']],
        ),
        new Get(
            normalizationContext: ['groups' => ['master_service:read']],
        ),
    ],
)]
#[ORM\Entity]
class MasterService
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['master:read', 'master_service:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Master::class, inversedBy: 'services')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Master $master = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['master:read', 'master_service:read'])]
    private ?string $name = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['master:read', 'master_service:read'])]
    private ?string $price = null;

    #[ORM\Column(type: 'integer')]
    #[Groups(['master:read', 'master_service:read'])]
    private ?int $durationMinutes = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['master:read', 'master_service:read'])]
    private ?string $categoryName = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMaster(): ?Master
    {
        return $this->master;
    }

    public function setMaster(?Master $master): self
    {
        $this->master = $master;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getDurationMinutes(): ?int
    {
        return $this->durationMinutes;
    }

    public function setDurationMinutes(int $durationMinutes): self
    {
        $this->durationMinutes = $durationMinutes;

        return $this;
    }

    public function getCategoryName(): ?string
    {
        return $this->categoryName;
    }

    public function setCategoryName(?string $categoryName): self
    {
        $this->categoryName = $categoryName;

        return $this;
    }
}
