<?php
namespace App\Service;

use App\Repository\CharacterRepository;
use DateTime; // on ajoute le use pour supprimer le \ dans setCreation()
use DateTimeImmutable;
use App\Entity\Character;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\CharacterType;
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
use App\Event\CharacterEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\PaginatorInterface;

class CharacterService implements CharacterServiceInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private CharacterRepository $characterRepository,
        private FormFactoryInterface $formFactory,
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
        $this->dispatcher->dispatch($event, CharacterEvent::CHARACTER_CREATED);

        $character->setSlug($this->slugger->slug($character->getName())->lower());
        $character->setIdentifier(hash('sha1', uniqid()));
        $character->setCreation(new DateTime());
        $character->setUpdatedAt(new DateTimeImmutable());
        $this->isEntityFilled($character);

        $this->em->persist($character);
        $this->em->flush();
        $this->dispatcher->dispatch($event, CharacterEvent::CHARACTER_CREATED_POST_DATABASE);
        return $character;
    }

    public function findAll(): array
    {
        return $this->characterRepository->findAll();
    }

    public function findAllPaginated($query): SlidingPagination
    {
        return $this->paginator->paginate(
            $this->findAll(), // On appelle la même requête
            $query->getInt('page', 1), // 1 par défaut
            min(100, $query->getInt('size', 10)) // 10 par défaut et 100 maximum
        );
    }

    public function update(Character $character, string $data): Character
    {
        $this->submit($character, CharacterType::class, $data);
        $event = new CharacterEvent($character);
        $this->dispatcher->dispatch($event, CharacterEvent::CHARACTER_UPDATED);
        $character->setSlug($this->slugger->slug($character->getName())->lower());
        $character->setUpdatedAt(new DateTimeImmutable());
        $this->isEntityFilled($character);
        // $character->setIdentifier(hash('sha1', uniqid())) -> supprimé pour ne pas le changer
        $this->em->persist($character);
        $this->em->flush();
        return $character;
    }

    public function delete(Character $character): void
    {
        $this->em->remove($character);
        $this->em->flush();
    }

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

    public function isEntityFilled(Character $character)
    {
        $errors = $this->validator->validate($character);
        if (count($errors) > 0) {
            $errorMsg = (string) $errors . 'Wrong data for Entity -> ';
            $errorMsg .= json_encode($this->serializeJson($character));
            throw new UnprocessableEntityHttpException($errorMsg);
        }
    }

    public function serializeJson($object)
    {
    $encoders = new JsonEncoder();
    $defaultContext = [
        AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
            return $object->getId();
        },
    ];

        $normalizers = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);
        $serializer = new Serializer([new DateTimeNormalizer(), $normalizers], [$encoders]);
        return $serializer->serialize($object, 'json');
    }
}