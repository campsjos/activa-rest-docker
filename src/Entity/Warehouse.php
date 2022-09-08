<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\WarehouseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WarehouseRepository::class)]
#[ApiResource(
    attributes: [
        'pagination_type' => 'page'
    ]
)]
class Warehouse extends Property
{
}
