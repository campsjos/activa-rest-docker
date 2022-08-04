<?php

namespace App\Controller;

use App\Entity\Location;
use App\Entity\Situation;
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
                if($situation->getChildByName($childName)) continue;

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

    #[Route('/import/locations', name: 'app_import_locations')]
    public function importLocations(): Response
    {
        $rawLocations = $this->hsXmlService->getLocations();
        $locations = [];

        foreach ($rawLocations as $locationName => $locationChildren) {
            /** @var Location */
            $location = $this->em->getRepository(Location::class)->findOneBy(['name' => $locationName]);
            if (!$location) {
                $location = new Location();
            }
            $location->setName($locationName);
            $location->setType('warehouse');
            foreach ($locationChildren as $childName => $grandChildren) {
                if($location->getChildByName($childName)) continue;

                $locationChild = new Location();
                $locationChild->setName($childName);
                $locationChild->setType('warehouse');
                $this->em->persist($locationChild);

                $location->addLocation($locationChild);

                foreach ($grandChildren as $grandChildName) {
                    if($location->getChildByName($grandChildName)) continue;
    
                    $locationGrandChild = new Location();
                    $locationGrandChild->setName($grandChildName);
                    $locationGrandChild->setType('warehouse');
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
}
