<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\PropertyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: PropertyRepository::class)]
#[ORM\InheritanceType(value: "SINGLE_TABLE")]
#[ORM\DiscriminatorColumn(name: "type", type: "string")]
#[ORM\DiscriminatorMap(value: [
    'warehouse' => 'Warehouse',
    'office' => 'Office',
    'residence' => 'Residence',
    'local' => 'Local',
])]
#[ApiResource]
class Property
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\ManyToMany(targetEntity: Feature::class)]
    protected Collection $features;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $address = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $postalCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $latitude = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $longitude = null;

    #[ORM\Column]
    protected ?bool $featured = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $image = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    protected array $gallery = [];

    #[ORM\Column(length: 255)]
    protected ?string $habitatsoftId = null;

    #[ORM\Column(length: 255)]
    protected ?string $operation = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $price = null;

    #[ORM\Column(length: 255)]
    protected ?string $priceSqm = null;

    #[ORM\Column(length: 255)]
    protected ?string $reference = null;

    #[ORM\ManyToMany(targetEntity: Service::class)]
    protected Collection $services;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $area = null;

    #[ORM\ManyToOne]
    protected ?Location $location = null;

    #[ORM\ManyToOne]
    protected ?Category $category = null;

    #[Gedmo\Timestampable(on: "create")]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    protected ?\DateTimeInterface $createdAt = null;

    #[Gedmo\Timestampable(on: "update")]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    protected ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->features = new ArrayCollection();
        $this->services = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Feature>
     */
    public function getFeatures(): Collection
    {
        return $this->features;
    }

    public function addFeature(Feature $feature): self
    {
        if (!$this->features->contains($feature)) {
            $this->features->add($feature);
        }

        return $this;
    }

    public function removeFeature(Feature $feature): self
    {
        $this->features->removeElement($feature);

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

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function isFeatured(): ?bool
    {
        return $this->featured;
    }

    public function setFeatured(bool $featured): self
    {
        $this->featured = $featured;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getGallery(): array
    {
        return $this->gallery;
    }

    public function setGallery(?array $gallery): self
    {
        $this->gallery = $gallery;

        return $this;
    }

    public function getHabitatsoftId(): ?string
    {
        return $this->habitatsoftId;
    }

    public function setHabitatsoftId(string $habitatsoftId): self
    {
        $this->habitatsoftId = $habitatsoftId;

        return $this;
    }

    public function getOperation(): ?string
    {
        return $this->operation;
    }

    public function setOperation(string $operation): self
    {
        $this->operation = $operation;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getPriceSqm(): ?string
    {
        return $this->priceSqm;
    }

    public function setPriceSqm(string $priceSqm): self
    {
        $this->priceSqm = $priceSqm;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * @return Collection<int, Service>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Service $service): self
    {
        if (!$this->services->contains($service)) {
            $this->services->add($service);
        }

        return $this;
    }

    public function removeService(Service $service): self
    {
        $this->services->removeElement($service);

        return $this;
    }

    public function getArea(): ?string
    {
        return $this->area;
    }

    public function setArea(?string $area): self
    {
        $this->area = $area;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
