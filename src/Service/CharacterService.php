<?php

namespace App\Service;

use DateTime; // on ajoute le use pour supprimer le \ dans setCreation()
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Character;
use App\Event\CharacterEvent;
use App\Repository\CharacterRepository;
use App\Form\CharacterType;
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
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;

class CharacterService implements CharacterServiceInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private FormFactoryInterface $formFactory,
        private CharacterRepository $characterRepository,
        private SluggerInterface $slugger,
        private ValidatorInterface $validator,
        private EventDispatcherInterface $dispatcher,
        private PaginatorInterface $paginator,
    ) {
    }

    // Creates the character
    public function create(string $data): Character
    {
        $character = new Character();

        $this->submit($character, CharacterType::class, $data);

        $event = new CharacterEvent($character);
        // Utilisation de la constante définie dans l'Event
        $this->dispatcher->dispatch($event, CharacterEvent::CHARACTER_CREATED);

        $character->setSlug($this->slugger->slug($character->getName())->lower());
        $character->setIdentifier(hash('sha1', uniqid()));
        $character->setCreation(new DateTime());
        $character->setModification(new DateTime());
        $this->isEntityFilled($character);

        $this->em->persist($character);
        $this->em->flush();

        $this->dispatcher->dispatch($event, CharacterEvent::CHARACTER_CREATED_POST_DATABASE);

        return $character;
    }

    // Finds all the characters
    public function findAll(): array
    {
        return $this->characterRepository->findAll();
    }

    // Updates the character
    public function update(Character $character, string $data): Character
    {
        $this->submit($character, CharacterType::class, $data);
        $character->setSlug($this->slugger->slug($character->getName())->lower());
        $character->setModification(new DateTime());
        $this->isEntityFilled($character);

        $this->em->persist($character);
        $this->em->flush();

        return $character;
    }

    // Delete the character
    public function delete(Character $character)
    {
        $this->em->remove($character);
        $this->em->flush();

        return true;
    }

    // Checks if the entity has been well filled
    public function isEntityFilled(Character $character)
    {
        // Vérification du bon fonctionnement en introduisant une erreur
        // $character->setIdentifier('badidentifier'); // Supprimer par la suite

        $errors = $this->validator->validate($character);
        if (count($errors) > 0) {
            $errorMsg = (string) $errors . 'Wrong data for Entity -> ';
            $errorMsg .= json_encode($this->serializeJson($character));
            throw new UnprocessableEntityHttpException($errorMsg);
        }
    }

    // Submits the form
    public function submit(Character $character, $formName, $data)
    {
        $dataArray = is_array($data) ? $data : json_decode($data, true);

        // Bad array
        if (null !== $data && !is_array($dataArray)) {
            throw new UnprocessableEntityHttpException('Submitted data is not an array -> ' . $data);
        }

        // Submits form
        $form = $this->formFactory->create($formName, $character, ['csrf_protection' => false]);
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
            ->withGroups(['character'])
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

    public function findAllPaginatedHealth($query, int $health): SlidingPagination
    {
        return $this->paginator->paginate(
            $this->characterRepository->findWithHealthLowerThanOrEqualTo($health), // On appelle la même requête
            $query->getInt('page', 1), // 1 par défaut
            min(100, $query->getInt('size', 10)) // 10 par défaut et 100 maximum
        );
    }

    // Defines the links for HATEOAS
    public function setLinks($object)
    {
        // Teste si l'objet est une pagination
        if($object instanceof SlidingPagination) {
            // Si oui, on boucle sur les items
            foreach ($object->getItems() as $item) {
                $this->setLinks($item);
            }
            return;
        }
        if(is_array($object)) {
            foreach($object as $item) {
                $this->setLinks($item);
            }
            return;
        }

        $links =[
            'self' => ['href' => '/characters/' . $object->getIdentifier()],
            'update' => ['href' => '/characters/' . $object->getIdentifier()],
            'delete' => ['href' => '/characters/' . $object->getIdentifier()]
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
            ->notPath('/buildings/')
            ->in($folder) // Dans le dossier images
        ;

        if($kind !== null) {
            $finder
                ->path('/' . $kind . '/')
            ;
        }
        $images = array();
        foreach ($finder as $file) {
            // dump($file); // Si vous voulez voir le contenu de file
            $images[] = str_replace(__DIR__ . '/../../public', '', $file->getPathname());
        }
        shuffle($images);
        return array_slice($images, 0, $number, true);
    }

}
