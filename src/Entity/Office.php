<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\OfficeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OfficeRepository::class)]
#[ApiResource(
    attributes: [
        'pagination_type' => 'page'
    ]
)]
class Office extends Property
{
}
