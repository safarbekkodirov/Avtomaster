<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Controller\MasterCreateAction;
use App\Controller\MasterMyProfileAction;
use App\Controller\MasterSearchAction;
use App\Controller\MasterServiceCreateAction;
use App\Controller\MasterServicesListAction;
use App\Entity\Interfaces\CreatedAtSettableInterface;
use App\Entity\Interfaces\DeletedAtSettableInterface;
use App\Entity\Interfaces\UpdatedAtSettableInterface;
use App\Entity\Traits\CreatedAtAccessorsTrait;
use App\Entity\Traits\DeletedAtAndByAccessorsTrait;
use App\Entity\Traits\UpdatedAtAndByAccessorsTrait;
use App\Repository\MasterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['masters:read']],
        ),
        new GetCollection(
            uriTemplate: 'masters/search',
            controller: MasterSearchAction::class,
            name: 'masterSearch',
            read: false,
            deserialize: false,
        ),
        new Get(
            uriTemplate: 'masters/me',
            controller: MasterMyProfileAction::class,
            security: "is_granted('ROLE_USER')",
            name: 'myProfile',
            normalizationContext: ['groups' => ['master:read']],
            read: false,
            deserialize: false,
        ),
        new Get(
            normalizationContext: ['groups' => ['master:read']],
        ),
        new Post(
            controller: MasterCreateAction::class,
            security: "is_granted('ROLE_USER')",
        ),
        new Patch(
            normalizationContext: ['groups' => ['master:read']],
            denormalizationContext: ['groups' => ['master:write']],
            security: "object.getUser() == user or is_granted('ROLE_ADMIN')",
        ),
        new Post(
            uriTemplate: 'masters/{id}/services',
            controller: MasterServiceCreateAction::class,
            security: "is_granted('ROLE_USER')",
            name: 'addService',
            read: false,
            deserialize: false,
        ),
        new Get(
            uriTemplate: 'masters/{id}/services',
            controller: MasterServicesListAction::class,
            name: 'listServices',
            read: false,
            deserialize: false,
        ),
        new Delete(
            security: "object.getUser() == user or is_granted('ROLE_ADMIN')",
        ),
    ],
    normalizationContext: ['groups' => ['master:read', 'masters:read']],
)]
#[ApiFilter(SearchFilter::class, properties: ['id' => 'exact', 'regionName' => 'partial'])]
#[ORM\Entity(repositoryClass: MasterRepository::class)]
class Master implements
    CreatedAtSettableInterface,
    UpdatedAtSettableInterface,
    DeletedAtSettableInterface
{
    use CreatedAtAccessorsTrait;
    use UpdatedAtAndByAccessorsTrait;
    use DeletedAtAndByAccessorsTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['master:read', 'masters:read'])]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Groups(['master:read', 'masters:read', 'master:write'])]
    private ?string $firstName = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Groups(['master:read', 'masters:read', 'master:write'])]
    private ?string $lastName = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    #[Groups(['master:read', 'masters:read', 'master:write'])]
    private ?string $phone = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['master:read', 'masters:read', 'master:write'])]
    private ?string $bio = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['master:read', 'masters:read', 'master:write'])]
    private ?string $regionName = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['master:read', 'masters:read', 'master:write'])]
    private ?string $address = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 7, nullable: true)]
    #[Groups(['master:read', 'masters:read', 'master:write'])]
    private ?string $lat = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 7, nullable: true)]
    #[Groups(['master:read', 'masters:read', 'master:write'])]
    private ?string $lng = null;

    #[ORM\Column(type: 'decimal', precision: 3, scale: 1, options: ['default' => 0])]
    #[Groups(['master:read', 'masters:read'])]
    private ?string $rating = '0.0';

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    #[Groups(['master:read', 'masters:read'])]
    private ?int $reviewsCount = 0;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(['master:read', 'masters:read'])]
    private ?bool $isVerified = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['master:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['master:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    #[ORM\OneToMany(targetEntity: MasterService::class, mappedBy: 'master', orphanRemoval: true, cascade: ['persist'])]
    #[Groups(['master:read'])]
    private Collection $services;

    public function __construct()
    {
        $this->services = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): self
    {
        $this->bio = $bio;

        return $this;
    }

    public function getRegionName(): ?string
    {
        return $this->regionName;
    }

    public function setRegionName(string $regionName): self
    {
        $this->regionName = $regionName;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getLat(): ?string
    {
        return $this->lat;
    }

    public function setLat(?string $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLng(): ?string
    {
        return $this->lng;
    }

    public function setLng(?string $lng): self
    {
        $this->lng = $lng;

        return $this;
    }

    public function getRating(): ?string
    {
        return $this->rating;
    }

    public function setRating(?string $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    public function getReviewsCount(): ?int
    {
        return $this->reviewsCount;
    }

    public function setReviewsCount(?int $reviewsCount): self
    {
        $this->reviewsCount = $reviewsCount;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(?bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection<int, MasterService>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(MasterService $service): self
    {
        if (!$this->services->contains($service)) {
            $this->services->add($service);
            $service->setMaster($this);
        }

        return $this;
    }

    public function removeService(MasterService $service): self
    {
        if ($this->services->removeElement($service)) {
            if ($service->getMaster() === $this) {
                $service->setMaster(null);
            }
        }

        return $this;
    }
}
