<?php

namespace App\Controller;

use App\Entity\Local;
use App\Entity\Location;
use App\Entity\Office;
use App\Entity\Property;
use App\Entity\Province;
use App\Entity\Residence;
use App\Entity\Service;
use App\Entity\Situation;
use App\Entity\Town;
use App\Entity\Warehouse;
use App\Entity\Zone;
use App\Service\HabitatsoftXmlService;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;

class ImportController extends AbstractController
{
    private HabitatsoftXmlService $hsXmlService;
    private ObjectManager $em;
    private HubInterface $hub;

    public function __construct(
        HabitatsoftXmlService $hsXmlService,
        ManagerRegistry $doctrine,
        HubInterface $hub
    ) {
        $this->hsXmlService = $hsXmlService;
        $this->em = $doctrine->getManager();
        $this->hub = $hub;
    }

    #[Route('/import/properties/{type}', name: 'app_import_properties')]
    public function importProperties(string $type): JsonResponse
    {
        // TODO: Use Mercure to notificate steps
        $properties = [];

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
        $this->sendFirstUpdate($propertyClass);

        $rawProperties = $this->hsXmlService->getProperties($type);

        $total = count($rawProperties);
        $progress = 0;
        $this->sendUpdate($propertyClass, $total, $progress);

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

                $province = $this->em->getRepository(Province::class)->findOneBy(["name" => $rawProperty["province"]]);
                $town = $province->findTownByName($rawProperty['town']);
                $zone = $town->findZoneByName($rawProperty['zone']);

                $property->setProvince($province);
                $property->setTown($town);
                $property->setZone($zone);

                $parentSituation = $this->em->getRepository(Situation::class)->findOneBy(['name' => $rawProperty['situation1']]);
                $childSituation = $this->em->getRepository(Situation::class)->findOneBy(['name' => $rawProperty['situation2']]);
                $property->setSituationParent($parentSituation);
                $property->setSituationChild($childSituation);

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

            $progress++;
            $this->sendUpdate($propertyClass, $total, $progress);

            $properties[] = $property->getReference();
        }

        $this->em->flush();
        return $this->json([
            'properties' => $properties,
        ]);
    }

    #[Route('/import/situations', name: 'app_import_situations')]
    public function importSituations(): JsonResponse
    {
        $this->sendFirstUpdate(Location::class);

        $rawSituations = $this->hsXmlService->getSituations();
        $situations = [];

        $total = count($rawSituations);
        $progress = 0;
        $this->sendUpdate(Location::class, $total, $progress);

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
            $situations[] = $situation->getName();
            $this->em->persist($situation);

            $progress++;
            $this->sendUpdate(Location::class, $total, $progress);
        }

        $this->em->flush();

        return $this->json([
            'situations' => $situations,
        ]);
    }

    #[Route('/import/locations/{type}', name: 'app_import_locations')]
    public function importLocations(string $type): JsonResponse
    {
        $this->sendFirstUpdate(Location::class);

        $rawLocations = $this->hsXmlService->getLocations($type);
        $locations = [];

        $total = count($rawLocations);
        $progress = 0;
        $this->sendUpdate(Location::class, $total, $progress);

        foreach ($rawLocations as $provinceName => $towns) {
            /** @var Province */
            $province = $this->em->getRepository(Province::class)->findOneBy(['name' => $provinceName]);
            if (!$province) {
                $province = new Province();
            }
            $province->setName($provinceName);
            $province->addType($type);
            $this->em->persist($province);
            foreach ($towns as $townName => $zones) {
                /** @var Town */
                $town = $this->em->getRepository(Town::class)->findByNameAndParent($townName, $province->getId());

                if (!$town) {
                    $town = new Town();
                    $town->setName($townName);
                }

                $town->addType($type);
                $this->em->persist($town);

                $province->addTown($town);

                foreach ($zones as $zoneName) {
                    /** @var Zone */
                    $zone = $this->em->getRepository(Zone::class)->findByNameAndParent($townName, $town->getId());

                    if (!$zone) {
                        $zone = new Zone();
                        $zone->setName($zoneName);
                    }

                    $zone->addType($type);
                    $this->em->persist($zone);

                    $town->addZone($zone);
                }
            }
            $locations[] = $province;
            $this->em->persist($province);

            $progress++;
            $this->sendUpdate(Location::class, $total, $progress);
        }

        $this->em->flush();

        return $this->json([
            'locations' => $locations,
        ]);
    }

    #[Route('/import/services', name: 'app_import_services')]
    public function importServices(): JsonResponse
    {
        $total = 8;
        $progress = 0;
        $this->sendUpdate(Service::class, $total, $progress);

        $services = $this->em->getRepository(Service::class)->findAll();

        if (count($services) > 0) {
            $progress = 8;
            $this->sendUpdate(Service::class, $total, $progress);

            return $this->json([
                'services' => $services,
            ]);
        }

        $translationRep = $this->em->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        $muelle = new Service();
        $translationRep
            ->translate($muelle, 'name', 'es', 'Muelle de carga')
            ->translate($muelle, 'name', 'ca', 'Moll de càrrega')
            ->translate($muelle, 'name', 'en', 'Loading bay')
            ->translate($muelle, 'name', 'fr', 'Quai de chargement');
        $muelle->addType(Property::TYPE_WAREHOUSE);

        $puenteGrua = new Service();
        $translationRep
            ->translate($puenteGrua, 'name', 'es', 'Puente Grúa')
            ->translate($puenteGrua, 'name', 'ca', 'Pont Grúa')
            ->translate($puenteGrua, 'name', 'en', 'Bridge Crane')
            ->translate($puenteGrua, 'name', 'fr', 'Pont Roulant');
        $puenteGrua->addType(Property::TYPE_WAREHOUSE);

        $antiincendios = new Service();
        $translationRep
            ->translate($antiincendios, 'name', 'es', 'Antiincendios')
            ->translate($antiincendios, 'name', 'ca', 'Antiincendis')
            ->translate($antiincendios, 'name', 'en', 'Fire system')
            ->translate($antiincendios, 'name', 'fr', 'Système d\'incendie');
        $antiincendios->addType(Property::TYPE_WAREHOUSE);

        $oficina = new Service();
        $translationRep
            ->translate($oficina, 'name', 'es', 'Oficina')
            ->translate($oficina, 'name', 'ca', 'Oficina')
            ->translate($oficina, 'name', 'en', 'Office')
            ->translate($oficina, 'name', 'fr', 'Bureau');
        $oficina->addType(Property::TYPE_WAREHOUSE);

        $diafana = new Service();
        $translationRep
            ->translate($diafana, 'name', 'es', 'Diafana')
            ->translate($diafana, 'name', 'ca', 'Diàfana')
            ->translate($diafana, 'name', 'en', 'Diaphanous')
            ->translate($diafana, 'name', 'fr', 'Diaphane');
        $diafana->addType(Property::TYPE_OFFICE);
        $diafana->addType(Property::TYPE_LOCAL);

        $divisiones = new Service();
        $translationRep
            ->translate($divisiones, 'name', 'es', 'Divisiones')
            ->translate($divisiones, 'name', 'ca', 'Divisions')
            ->translate($divisiones, 'name', 'en', 'Divisions')
            ->translate($divisiones, 'name', 'fr', 'Divisions');
        $divisiones->addType(Property::TYPE_OFFICE);
        $divisiones->addType(Property::TYPE_LOCAL);

        $fincaRegia = new Service();
        $translationRep
            ->translate($fincaRegia, 'name', 'es', 'Finca Régia')
            ->translate($fincaRegia, 'name', 'ca', 'Finca Règia')
            ->translate($fincaRegia, 'name', 'en', 'Regal State')
            ->translate($fincaRegia, 'name', 'fr', 'Domaine Royal');
        $fincaRegia->addType(Property::TYPE_OFFICE);

        $escaparate = new Service();
        $translationRep
            ->translate($escaparate, 'name', 'es', 'Escaparate')
            ->translate($escaparate, 'name', 'ca', 'Aparador')
            ->translate($escaparate, 'name', 'en', 'Showcase')
            ->translate($escaparate, 'name', 'fr', 'Vitrine');
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

        $progress = 8;
        $this->sendUpdate(Service::class, $total, $progress);

        return $this->json([
            'services' => $services,
        ]);
    }

    // Don't import categories until data is normalized by client
    #[Route('/import/categories', name: 'app_import_categories')]
    public function importCategories(): JsonResponse
    {
        $categories = $this->hsXmlService->getCategories();
        return $this->json([
            'categories' => $categories,
        ]);
    }

    #[Route('/import/clean/properties', name: 'app_import_clean_properties')]
    public function cleanProperties(): JsonResponse
    {
        $references = $this->hsXmlService->getPropertyReferences();
        $removedRows = $this->em->getRepository(Property::class)->deleteByNotPresentInReferences($references);

        return $this->json([
            'removedRows' => $removedRows
        ]);
    }

    #[Route('/import/clean/locations', name: 'app_import_clean_locations')]
    public function cleanLocations(): JsonResponse
    {
        $removedRows = 0;
        $provinces = $this->em->getRepository(Province::class)->findAll();
        $removedRows += $this->removeLocationIfHasNoProperties($provinces);

        $towns = $this->em->getRepository(Town::class)->findAll();
        $removedRows += $this->removeLocationIfHasNoProperties($towns);

        $zones = $this->em->getRepository(Zone::class)->findAll();
        $removedRows += $this->removeLocationIfHasNoProperties($zones);

        return $this->json([
            'removedRows' => $removedRows
        ]);
    }

    /**
     * Removes locations if doesn't relate to any property
     *
     * @param array $locations
     * @return integer
     */
    private function removeLocationIfHasNoProperties(array $locations): int
    {
        $removedRows = 0;
        foreach ($locations as $location) {
            if ($location->getProperties()->count() === 0) {
                $this->em->remove($location);
                $removedRows++;
            }
        }
        $this->em->flush();
        return $removedRows;
    }

    #[Route('/import/clean/situations', name: 'app_import_clean_situations')]
    public function cleanSituations(): JsonResponse
    {
        $removedRows = 0;
        /** @var ?Collection<int, Situation> */
        $situations = $this->em->getRepository(Situation::class)->findAll();
        foreach ($situations as $situation) {
            if ($situation->getParentProperties()->count() === 0 && $situation->getChildProperties()->count() === 0) {
                $this->em->remove($situation);
                $removedRows++;
            }
        }

        return $this->json([
            'removedRows' => $removedRows
        ]);
    }

    private function sendFirstUpdate($className)
    {
        $updateData = [
            'message' => "Importando " . $className . '... ',
            'status' => 'processing'
        ];
        $update = new Update(
            'import-status',
            json_encode($updateData)
        );
        $this->hub->publish($update);
    }

    private function sendUpdate($className, $total, $progress)
    {
        $updateData = [
            'message' => "Importando " . $className . '... ' . $progress . '/' . $total,
            'status' => $progress < $total ? 'processing' : 'success'
        ];
        $update = new Update(
            'import-status',
            json_encode($updateData)
        );
        $this->hub->publish($update);
    }
}
