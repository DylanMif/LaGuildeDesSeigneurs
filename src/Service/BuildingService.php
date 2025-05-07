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
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use App\Event\BuildingEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\PaginatorInterface;

class BuildingService implements BuildingServiceInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private BuildingRepository $buildingRepository,
        private FormFactoryInterface $formFactory,
        private SluggerInterface $slugger,
        private ValidatorInterface $validator,
        private EventDispatcherInterface $dispatcher,
        private PaginatorInterface $paginator,
    ) {
    }

    public function findAll(): array
    {
        return $this->buildingRepository->findAll();
    }

    public function findAllPaginated($query): SlidingPagination
    {
        return $this->paginator->paginate(
            $this->findAll(),
            $query->getInt('page', 1),
            min(100, $query->getInt('size', 10))
        );
    }

    public function serializeJson($object)
    {
        $encoders = new JsonEncoder();
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
                return $object->getId(); // Ce qu'il doit retourner
            },
        ];
        $normalizers = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);
        $serializer = new Serializer([new DateTimeNormalizer(), $normalizers], [$encoders]);
        return $serializer->serialize($object, 'json');
    }

    public function create(string $data): Building
    {
        $building = new Building();
        $this->submit($building, BuildingType::class, $data);
        $event = new BuildingEvent($building);
        $this->dispatcher->dispatch($event, BuildingEvent::BUILDING_CREATED);
        $building->setIdentifier(hash('sha1', uniqid()));
        $building->setPrice(500);
        $building->setSlug($this->slugger->slug($building->getName())->lower());
        $this->isEntityFilled($building);

        $this->em->persist($building);
        $this->em->flush();
        $this->dispatcher->dispatch($event, BuildingEvent::BUILDING_CREATED_POST_DATABASE);
        return $building;
    }

    public function update(Building $building, string $data): Building
    {
        $this->submit($building, BuildingType::class, $data);
        $event = new BuildingEvent($building);
        $this->dispatcher->dispatch($event, BuildingEvent::BUILDING_UPDATED);
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
        $errors = $this->validator->validate($building);
        if (count($errors) > 0) {
            $errorMsg = (string) $errors . 'Wrong data for Entity -> ';
            $errorMsg .= json_encode($this->serializeJson($building));
            throw new UnprocessableEntityHttpException($errorMsg);
        }
    }

}