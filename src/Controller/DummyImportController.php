<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Feature;
use App\Entity\Location;
use App\Entity\Property;
use App\Entity\Service;
use App\Entity\Warehouse;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DummyImportController extends AbstractController
{
    #[Route('/dummy/show', name: 'app_dummy_show')]
    public function show(ManagerRegistry $doctrine): Response
    {
        $properties = $doctrine->getRepository(Property::class)->findAll();

        return $this->render('dummy_import/show.html.twig', [
            'properties' => $properties,
        ]);

    }

    #[Route('/dummy/import', name: 'app_dummy_import')]
    public function import(EntityManagerInterface $em): Response
    {

        $faker = \Faker\Factory::create();

        $location1 = new Location();
        $location1->setName("Barcelona");
        $location1->setType('warehouse');

        $location2 = new Location();
        $location2->setName("Sant Andreu");
        $location2->setParent($location1);
        $location2->setType('warehouse');

        $service = new Service();
        $service->setName('Extintor');
        $service->setType('warehouse');

        $category = new Category();
        $category->setName('Nave industrial');
        $category->setType('warehouse');
        
        $feature = new Feature();
        $feature->setName("Oficinas");
        $feature->setType('warehouse');

        $property = new Warehouse();
        $property->setName("Apartamento en Sant Andreu");
        $property->setAddress($faker->address());
        $property->setArea(100);
        $property->setCategory($category);
        $property->setFeatured(true);
        $property->setGallery([
            $faker->imageUrl(640, 480, 'animals', true), 
            $faker->imageUrl(640, 480, 'animals', true), 
            $faker->imageUrl(640, 480, 'animals', true)
        ]);
        $property->setHabitatsoftId($faker->numberBetween(1000, 9999));
        $property->setImage($faker->imageUrl(640, 480, 'animals', true));
        $property->setLatitude($faker->longitude());
        $property->setLongitude($faker->latitude());
        $property->setLocation($location1);
        $property->setOperation('rent');
        $property->setPostalCode($faker->postcode());
        $property->setPrice("2000");
        $property->setPriceSqm("200");
        $property->setReference($faker->numberBetween(1000, 9999));
        $property->addFeature($feature);
        $property->addService($service);

        $em->persist($location1);
        $em->persist($service);
        $em->persist($category);
        $em->persist($feature);
        $em->persist($property);

        $em->flush();

        return $this->render('dummy_import/import.html.twig', [
            'property' => $property,
        ]);
    }
}
