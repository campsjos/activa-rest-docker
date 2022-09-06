<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\SituationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: SituationRepository::class)]
#[ApiResource]
class Situation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Gedmo\Slug(fields: ['name'])]
    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'situations')]
    private ?self $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    private Collection $situations;

    #[ORM\OneToMany(mappedBy: 'situationParent', targetEntity: Property::class)]
    private Collection $parentProperties;

    #[ORM\OneToMany(mappedBy: 'situationChild', targetEntity: Property::class)]
    private Collection $childProperties;

    public function __construct()
    {
        $this->situations = new ArrayCollection();
        $this->parentProperties = new ArrayCollection();
        $this->childProperties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getSituations(): Collection
    {
        return $this->situations;
    }

    public function addSituation(self $situation): self
    {
        if (!$this->situations->contains($situation)) {
            $this->situations->add($situation);
            $situation->setParent($this);
        }

        return $this;
    }

    public function removeSituation(self $situation): self
    {
        if ($this->situations->removeElement($situation)) {
            // set the owning side to null (unless already changed)
            if ($situation->getParent() === $this) {
                $situation->setParent(null);
            }
        }

        return $this;
    }

    public function getChildByName(string $name): ?Situation
    {
        foreach ($this->situations as $situation) {
            if ($name === $situation->getName()) {
                return $situation;
            }
        }

        return null;
    }

    /**
     * @return Collection<int, Property>
     */
    public function getParentProperties(): Collection
    {
        return $this->parentProperties;
    }

    public function addParentProperty(Property $childProperty): self
    {
        if (!$this->parentProperties->contains($childProperty)) {
            $this->parentProperties->add($childProperty);
            $childProperty->setSituationParent($this);
        }

        return $this;
    }

    public function removeParentProperty(Property $childProperty): self
    {
        if ($this->parentProperties->removeElement($childProperty)) {
            // set the owning side to null (unless already changed)
            if ($childProperty->getSituationParent() === $this) {
                $childProperty->setSituationParent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Property>
     */
    public function getChildProperties(): Collection
    {
        return $this->childProperties;
    }

    public function addChildProperty(Property $childProperty): self
    {
        if (!$this->childProperties->contains($childProperty)) {
            $this->childProperties->add($childProperty);
            $childProperty->setSituationChild($this);
        }

        return $this;
    }

    public function removeChildProperty(Property $childProperty): self
    {
        if ($this->childProperties->removeElement($childProperty)) {
            // set the owning side to null (unless already changed)
            if ($childProperty->getSituationChild() === $this) {
                $childProperty->setSituationChild(null);
            }
        }

        return $this;
    }
}
