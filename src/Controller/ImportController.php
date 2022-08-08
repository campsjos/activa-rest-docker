<?php

namespace App\Controller;

use App\Entity\Local;
use App\Entity\Location;
use App\Entity\Office;
use App\Entity\Property;
use App\Entity\Residence;
use App\Entity\Service;
use App\Entity\Situation;
use App\Entity\Warehouse;
use App\Service\HabitatsoftXmlService;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImportController extends AbstractController
{
    private HabitatsoftXmlService $hsXmlService;
    private ObjectManager $em;

    public function __construct(HabitatsoftXmlService $hsXmlService, ManagerRegistry $doctrine)
    {
        $this->hsXmlService = $hsXmlService;
        $this->em = $doctrine->getManager();
    }

    #[Route('/import/properties/{type}', name: 'app_import_properties')]
    public function importProperties(string $type): Response
    {

        // TODO: Add translations
        // TODO: Remove non existent Properties
        // TODO: Flush entities

        $properties = [];
        $rawProperties = $this->hsXmlService->getProperties($type);

        $propertyClass = "";
        switch ($type) {
            case Property::TYPE_WAREHOUSE:
                $propertyClass = Warehouse::class;
                break;
            case Property::TYPE_OFFICE:
                $propertyClass = Office::class;
                break;
            case Property::TYPE_LOCAL:
                $propertyClass = Local::class;
                break;
            case Property::TYPE_RESIDENCE:
                $propertyClass = Residence::class;
                break;
        }

        foreach ($rawProperties as $rawProperty) {
            $property = $this->em->getRepository($propertyClass)->findOneBy(['reference' => $rawProperty["reference"]]);
            if (!$property) {
                /** @var Office|Warehouse|Local|Residence */
                $property = new $propertyClass;
                $property->setReference($rawProperty['reference']);
                $property->setAddress($rawProperty['address']);
                $property->setPostalCode($rawProperty['postalCode']);
                $property->setLatitude($rawProperty['latitude']);
                $property->setLongitude($rawProperty['longitude']);
                $property->setFeatured($rawProperty['featured']);
                $property->setImage($rawProperty['image']);
                $property->setGallery($rawProperty['gallery']);
                $property->setHabitatsoftId($rawProperty['habitatsoftId']);
                $property->setOperation($rawProperty['operation']);
                $property->setPrice($rawProperty['price']);
                $property->setPriceSqm($rawProperty['priceSqm']);
                $property->setArea($rawProperty['area']);

                $services = $this->em->getRepository(Service::class)->findBy(['name' => $rawProperty["services"]]);
                $property->setServices($services);

                $province = $this->em->getRepository(Location::class)->findFirstLevelByName($rawProperty["province"]);
                $town = $this->em->getRepository(Location::class)->findOneBy(['name' => $rawProperty['town'], 'parent' => $province]);
                $zone = $this->em->getRepository(Location::class)->findOneBy(['name' => $rawProperty['zone'], 'parent' => $town]);
                $property->setProvince($province);
                $property->setTown($town);
                $property->setZone($zone);

                $translationRep = $this->em->getRepository('Gedmo\\Translatable\\Entity\\Translation');
                $translationRep
                    ->translate($property, 'name', 'es', $rawProperty['translations']['es']['title'])
                    ->translate($property, 'name', 'ca', $rawProperty['translations']['ct']['title'])
                    ->translate($property, 'name', 'en', $rawProperty['translations']['en']['title'])
                    ->translate($property, 'name', 'fr', $rawProperty['translations']['fr']['title'])
                    ->translate($property, 'body', 'es', $rawProperty['translations']['es']['body'])
                    ->translate($property, 'body', 'ca', $rawProperty['translations']['ct']['body'])
                    ->translate($property, 'body', 'en', $rawProperty['translations']['en']['body'])
                    ->translate($property, 'body', 'fr', $rawProperty['translations']['fr']['body']);

                $this->em->persist($property);
            }

            
            $properties[] = $property;
        }
        dump($property);

        return $this->render('import/properties.html.twig', [
            'properties' => $properties,
        ]);
    }

    #[Route('/import/situations', name: 'app_import_situations')]
    public function importSituations(): Response
    {
        $rawSituations = $this->hsXmlService->getSituations();
        $situations = [];

        foreach ($rawSituations as $situationName => $situationChildren) {
            /** @var Situation */
            $situation = $this->em->getRepository(Situation::class)->findOneBy(['name' => $situationName]);
            if (!$situation) {
                $situation = new Situation();
            }
            $situation->setName($situationName);
            foreach ($situationChildren as $childName) {
                if ($situation->getChildByName($childName)) continue;

                $situationChild = new Situation();
                $situationChild->setName($childName);
                $this->em->persist($situationChild);

                $situation->addSituation($situationChild);
            }
            $situations[] = $situation;
            $this->em->persist($situation);
        }

        $this->em->flush();

        return $this->render('import/situations.html.twig', [
            'situations' => $situations,
        ]);
    }

    #[Route('/import/locations/{type}', name: 'app_import_locations')]
    public function importLocations(string $type): Response
    {
        $rawLocations = $this->hsXmlService->getLocations($type);
        $locations = [];

        foreach ($rawLocations as $locationName => $locationChildren) {
            /** @var Location */
            $location = $this->em->getRepository(Location::class)->findOneBy(['name' => $locationName]);
            if (!$location) {
                $location = new Location();
            }
            $location->setName($locationName);
            $location->addType($type);
            foreach ($locationChildren as $childName => $grandChildren) {
                $locationChild = $location->getChildByName($childName);

                if (!$locationChild) {
                    $locationChild = new Location();
                    $locationChild->setName($childName);
                }

                $locationChild->addType($type);
                $this->em->persist($locationChild);

                $location->addLocation($locationChild);

                foreach ($grandChildren as $grandChildName) {
                    $locationGrandChild = $locationChild->getChildByName($grandChildName);

                    if (!$locationGrandChild) {
                        $locationGrandChild = new Location();
                        $locationGrandChild->setName($grandChildName);
                    }

                    $locationGrandChild->addType($type);
                    $this->em->persist($locationGrandChild);

                    $locationChild->addLocation($locationGrandChild);
                }
            }
            $locations[] = $location;
            $this->em->persist($location);
        }

        $this->em->flush();

        return $this->render('import/locations.html.twig', [
            'locations' => $locations,
        ]);
    }

    #[Route('/import/services', name: 'app_import_services')]
    public function importServices(): Response
    {
        $muelle = new Service();
        $muelle->setName("Muelle");
        $muelle->addType(Property::TYPE_WAREHOUSE);

        $puenteGrua = new Service();
        $puenteGrua->setName("Puente Grúa");
        $puenteGrua->addType(Property::TYPE_WAREHOUSE);

        $antiincendios = new Service();
        $antiincendios->setName("Antiincendios");
        $antiincendios->addType(Property::TYPE_WAREHOUSE);

        $oficina = new Service();
        $oficina->setName("Oficina");
        $oficina->addType(Property::TYPE_WAREHOUSE);

        $diafana = new Service();
        $diafana->setName("Diáfana");
        $diafana->addType(Property::TYPE_OFFICE);
        $diafana->addType(Property::TYPE_LOCAL);

        $divisiones = new Service();
        $divisiones->setName("Divisiones");
        $divisiones->addType(Property::TYPE_OFFICE);
        $divisiones->addType(Property::TYPE_LOCAL);

        $fincaRegia = new Service();
        $fincaRegia->setName("Finca Regia");
        $fincaRegia->addType(Property::TYPE_OFFICE);

        $escaparate = new Service();
        $escaparate->setName("Escaparate");
        $escaparate->addType(Property::TYPE_LOCAL);

        $this->em->persist($muelle);
        $this->em->persist($puenteGrua);
        $this->em->persist($antiincendios);
        $this->em->persist($oficina);
        $this->em->persist($diafana);
        $this->em->persist($divisiones);
        $this->em->persist($fincaRegia);
        $this->em->persist($escaparate);

        $this->em->flush();

        /** @var Service[] */
        $services = $this->em->getRepository(Service::class)->findAll();

        return $this->render('import/services.html.twig', [
            'services' => $services,
        ]);
    }

    // Don't import categories until data is normalized by client
    #[Route('/import/categories', name: 'app_import_categories')]
    public function importCategories(): Response
    {
        $categories = $this->hsXmlService->getCategories();
        return $this->render('import/categories.html.twig', [
            'categories' => $categories,
        ]);
    }
}
