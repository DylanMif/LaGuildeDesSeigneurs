<?php
namespace App\Service;

use App\Entity\Building;
use App\Repository\BuildingRepository;
use App\Repository\CharacterRepository;
use DateTime; // on ajoute le use pour supprimer le \ dans setCreation()
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\BuildingType;
use LogicException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\String\Slugger\SluggerInterface;

class BuildingService implements BuildingServiceInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private BuildingRepository $buildingRepository,
        private FormFactoryInterface $formFactory,
        private SluggerInterface $slugger
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

    public function create(string $data): Building
    {
        $building = new Building();
        $this->submit($building, BuildingType::class, $data);
        $building->setIdentifier(hash('sha1', uniqid()));
        $building->setPrice(500);
        $building->setSlug($this->slugger->slug($building->getName())->lower());
        $this->isEntityFilled($building);

        $this->em->persist($building);
        $this->em->flush();
        return $building;
    }

    public function update(Building $building, string $data): Building
    {
        $this->submit($building, BuildingType::class, $data);
        $building->setSlug($this->slugger->slug($building->getName())->lower());
        $building->setPrice(15000);
        $this->isEntityFilled($building);

        $this->em->persist($building);
        $this->em->flush();
        return $building;
    }

    public function delete(Building $building): void
    {
        $this->em->remove($building);
        $this->em->flush();
    }

    public function submit(Building $building, $formName, $data)
    {
        $dataArray = is_array($data) ? $data : json_decode($data, true);
        // Bad array
        if (null !== $data && !is_array($dataArray)) {
            throw new UnprocessableEntityHttpException('Submitted data is not an array -> ' . $data);
        }
        // Submits form
        $form = $this->formFactory->create($formName, $building, ['csrf_protection' => false]);
        $form->submit($dataArray, false);// With false, only submitted fields are validated
        // Gets errors
        $errors = $form->getErrors();
        foreach ($errors as $error) {
            $errorMsg = 'Error ' . get_class($error->getCause());
            $errorMsg .= ' --> ' . $error->getMessageTemplate();
            $errorMsg .= ' ' . json_encode($error->getMessageParameters());
            throw new LogicException($errorMsg);
        }
    }

    public function isEntityFilled(Building $building)
    {
        if (null === $building->getName() ||
        null === $building->getCaste() ||
        null === $building->getStrength() ||
        null === $building->getSlug() ||
        null === $building->getIdentifier()
        ) {
            $errorMsg = 'Missing data for Entity -> ' . json_encode($building->toArray());
            throw new UnprocessableEntityHttpException($errorMsg);
        }
    }

}