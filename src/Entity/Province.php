<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ProvinceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProvinceRepository::class)]
#[ApiResource]
class Province extends Location
{
    #[ORM\OneToMany(mappedBy: 'province', targetEntity: Town::class, orphanRemoval: true)]
    private ?Collection $towns;

    #[ORM\OneToMany(mappedBy: 'province', targetEntity: Property::class)]
    private Collection $properties;

    public function __construct()
    {
        parent::__construct();
        $this->towns = new ArrayCollection();
        $this->properties = new ArrayCollection();
    }

    /**
     * @return Collection<int, Town>
     */
    public function getTowns(): Collection
    {
        return $this->towns;
    }

    public function addTown(Town $town): self
    {
        if (!$this->towns->contains($town)) {
            $this->towns->add($town);
            $town->setProvince($this);
        }

        return $this;
    }

    public function removeTown(Town $town): self
    {
        if ($this->towns->removeElement($town)) {
            // set the owning side to null (unless already changed)
            if ($town->getProvince() === $this) {
                $town->setProvince(null);
            }
        }

        return $this;
    }

    /**
     * @return ?Town
     */
    public function findTownByName(string $townName): ?Town
    {
        foreach ($this->towns as $town ) {
            if($town->getName() === $townName) {
                return $town;
            }
        }
        return null;
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
            $property->setProvince($this);
        }

        return $this;
    }

    public function removeProperty(Property $property): self
    {
        if ($this->properties->removeElement($property)) {
            // set the owning side to null (unless already changed)
            if ($property->getProvince() === $this) {
                $property->setProvince(null);
            }
        }

        return $this;
    }

}
