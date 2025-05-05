<?php
namespace App\Service;

use App\Entity\Building;
use App\Repository\BuildingRepository;
use App\Repository\CharacterRepository;
use DateTime; // on ajoute le use pour supprimer le \ dans setCreation()
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class BuildingService implements BuildingServiceInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private BuildingRepository $buildingRepository
    ) {
    }

    public function findAll(): array
    {
        $buildingsFinal = array();
        $buildings = $this->buildingRepository->findAll();
        foreach ($buildings as $building) {
            $buildingsFinal[] = $building->toArray();
        }
        return $buildingsFinal;
    }

    public function create(): Building
    {
        $building = new Building();
        $building->setName("Château Lenora");
        $building->setSlug("chateau_lenora");
        $building->setCaste("castle");
        $building->setStrength(50);
        $building->setImage("buildings/chateau_lenora");
        $building->setIdentifier(hash('sha1', uniqid()));

        $this->em->persist($building);
        $this->em->flush();
        return $building;
    }

    public function update(Building $building): Building
    {
        $building->setName("Château Silken");
        $building->setSlug("chateau_silken");
        $building->setCaste("castle");
        $building->setStrength(75);
        $building->setImage("buildings/chateau_silken");

        $this->em->persist($building);
        $this->em->flush();
        return $building;
    }

    public function delete(Building $building): void
    {
        $this->em->remove($building);
        $this->em->flush();
    }

}