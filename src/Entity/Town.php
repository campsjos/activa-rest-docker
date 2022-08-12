<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\TownRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TownRepository::class)]
#[ApiResource]
class Town extends Location
{
    #[ORM\ManyToOne(inversedBy: 'towns')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Province $province = null;

    #[ORM\OneToMany(mappedBy: 'town', targetEntity: Zone::class, orphanRemoval: true)]
    private Collection $zones;

    #[ORM\OneToMany(mappedBy: 'town', targetEntity: Property::class)]
    private Collection $properties;

    public function __construct()
    {
        parent::__construct();
        $this->zones = new ArrayCollection();
        $this->properties = new ArrayCollection();
    }

    public function getProvince(): ?Province
    {
        return $this->province;
    }

    public function setProvince(?Province $province): self
    {
        $this->province = $province;

        return $this;
    }

    /**
     * @return Collection<int, Zone>
     */
    public function getZones(): Collection
    {
        return $this->zones;
    }

    public function addZone(Zone $zone): self
    {
        if (!$this->zones->contains($zone)) {
            $this->zones->add($zone);
            $zone->setTown($this);
        }

        return $this;
    }

    public function removeZone(Zone $zone): self
    {
        if ($this->zones->removeElement($zone)) {
            // set the owning side to null (unless already changed)
            if ($zone->getTown() === $this) {
                $zone->setTown(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Property>
     */
    public function getProperties(): Collection
    {
        return $this->properties;
    }

    public function addProperty(Property $property): self
    {
        if (!$this->properties->contains($property)) {
            $this->properties->add($property);
            $property->setTown($this);
        }

        return $this;
    }

    public function removeProperty(Property $property): self
    {
        if ($this->properties->removeElement($property)) {
            // set the owning side to null (unless already changed)
            if ($property->getTown() === $this) {
                $property->setTown(null);
            }
        }

        return $this;
    }

    /**
     * @return ?Zone
     */
    public function findZoneByName(string $zoneName): ?Zone
    {
        foreach ($this->zones as $zone ) {
            if($zone->getName() === $zoneName) {
                return $zone;
            }
        }
        return null;
    }

}
