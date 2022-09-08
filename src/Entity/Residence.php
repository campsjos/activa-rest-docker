<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ResidenceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResidenceRepository::class)]
#[ApiResource(
    attributes: [
        'pagination_type' => 'page'
    ]
)]
class Residence extends Property
{
    #[ORM\Column(nullable: true)]
    private ?int $rooms = null;

    #[ORM\Column(nullable: true)]
    private ?int $baths = null;

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
