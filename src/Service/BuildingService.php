<?php

namespace App\Service;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Building;
use App\Event\BuildingEvent;
use App\Repository\BuildingRepository;
use App\Form\BuildingType;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\PaginatorInterface;
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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
Use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;

class BuildingService implements BuildingServiceInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private FormFactoryInterface $formFactory,
        private BuildingRepository $buildingRepository,
        private SluggerInterface $slugger,
        private ValidatorInterface $validator,
        private EventDispatcherInterface $dispatcher,
        private PaginatorInterface $paginator,
    ) {
    }

    // Creates the building
    public function create(string $data): Building
    {
        $building = new Building();

        $this->submit($building, BuildingType::class, $data);

        $event = new BuildingEvent($building);
        // Utilisation de la constante définie dans l'Event
        $this->dispatcher->dispatch($event, BuildingEvent::BUILDING_CREATED);

        $building->setSlug($this->slugger->slug($building->getName())->lower());
        $building->setIdentifier(hash('sha1', uniqid()));
        $building->setCreation(new DateTime());
        $building->setModification(new DateTime());
        $this->isEntityFilled($building);

        $this->em->persist($building);
        $this->em->flush();

        $this->dispatcher->dispatch($event, BuildingEvent::BUILDING_CREATED_POST_DATABASE);

        return $building;
    }

    // Finds all the buildings
    public function findAll(): array
    {
        return $this->buildingRepository->findAll();
    }

    // Updates the building
    public function update(Building $building, string $data): Building
    {
        $this->submit($building, BuildingType::class, $data);

        $event = new BuildingEvent($building);
        // Utilisation de la constante définie dans l'Event
        $this->dispatcher->dispatch($event, BuildingEvent::BUILDING_UPDATED);

        $building->setSlug($this->slugger->slug($building->getName())->lower());
        $building->setModification(new DateTime());
        $this->isEntityFilled($building);

        $this->em->persist($building);
        $this->em->flush();

        return $building;
    }

    // Delete the building
    public function delete(Building $building)
    {
        $this->em->remove($building);
        $this->em->flush();

        return true;
    }

    // Checks if the entity has been well filled
    public function isEntityFilled(Building $building)
    {
        $errors = $this->validator->validate($building);
        if (count($errors) > 0) {
            $errorMsg = (string) $errors . 'Wrong data for Entity -> ';
            $errorMsg .= json_encode($this->serializeJson($building));
            throw new UnprocessableEntityHttpException($errorMsg);
        }
    }

    // Submits the form
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

    // Serializes the object(s)
    public function serializeJson($object)
    {
        $encoders = new JsonEncoder();
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
                return $object->getId();
            },
        ];

        $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());
        $normalizers = new ObjectNormalizer($classMetadataFactory, null, null, null, null, null, $defaultContext);
        $serializer = new Serializer([new DateTimeNormalizer(), $normalizers], [$encoders]);

        $this->setLinks($object);

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups(['building'])
            ->toArray()
        ;

        return $serializer->serialize($object, 'json', $context);
    }

    // Finds all characters paginated
    public function findAllPaginated($query): SlidingPagination
    {
        return $this->paginator->paginate(
            $this->findAll(), // On appelle la même requête
            $query->getInt('page', 1), // 1 par défaut
            min(100, $query->getInt('size', 10)) // 10 par défaut et 100 maximum
        );
    }

    // Defines the links for HATEOAS
    public function setLinks($object)
    {

        // Teste si l'objet est une pagination
        if($object instanceof SlidingPagination) {
            foreach ($object->getItems() as $item) {
                $this->setLinks($item);
            }
            return;
        }
        
        $links =[
            'self' => ['href' => '/buildings/' . $object->getIdentifier()],
            'update' => ['href' => '/buildings/' . $object->getIdentifier()],
            'delete' => ['href' => '/buildings/' . $object->getIdentifier()]
        ];
        $object->setLinks($links);
    }

    // Gets random images
    public function getImages(int $number, ?string $kind = null): array
    {
        $folder = __DIR__ . '/../../public/images/';
        $finder = new Finder();
        $finder
            ->files() // On veut des fichiers
            ->path('/buildings/')
            ->in($folder) // Dans le dossier images
        ;
        
        $images = array();
        foreach ($finder as $file) {
            // dump($file); // Si vous voulez voir le contenu de file
            $images[] = str_replace(__DIR__ . '/../../public', '', $file->getPathname());
        }
        shuffle($images);
        return array_slice($images, 0, $number, true);
    }
}
