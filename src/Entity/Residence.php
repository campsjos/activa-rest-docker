<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ResidenceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResidenceRepository::class)]
#[ApiResource]
class Residence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $rooms = null;

    #[ORM\Column(nullable: true)]
    private ?int $baths = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRooms(): ?int
    {
        return $this->rooms;
    }

    public function setRooms(?int $rooms): self
    {
        $this->rooms = $rooms;

        return $this;
    }

    public function getBaths(): ?int
    {
        return $this->baths;
    }

    public function setBaths(?int $baths): self
    {
        $this->baths = $baths;

        return $this;
    }
}
