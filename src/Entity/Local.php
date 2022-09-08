<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\LocalRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LocalRepository::class)]
#[ApiResource(
    attributes: [
        'pagination_type' => 'page'
    ]
)]
class Local extends Property
{
}
