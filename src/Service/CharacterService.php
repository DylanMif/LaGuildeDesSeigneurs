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

class CharacterService implements CharacterServiceInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private CharacterRepository $characterRepository,
        private FormFactoryInterface $formFactory,
        private SluggerInterface $slugger,
    ) {
    }
    // Creates the character
    public function create(string $data): Character
    {
        $character = new Character();
        $this->submit($character, CharacterType::class, $data);
        $character->setSlug($this->slugger->slug($character->getName())->lower());
        $character->setIdentifier(hash('sha1', uniqid()));
        $character->setCreation(new DateTime());
        $character->setUpdatedAt(new DateTimeImmutable());
        $this->isEntityFilled($character);

        $this->em->persist($character);
        $this->em->flush();
        return $character;
    }

    public function findAll(): array
    {
        $charactersFinal = array();
        $characters = $this->characterRepository->findAll();
        foreach ($characters as $character) {
            $charactersFinal[] = $character->toArray();
        }
        return $charactersFinal;
    }

    public function update(Character $character, string $data): Character
    {
        $this->submit($character, CharacterType::class, $data);
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
        if (null === $character->getKind() ||
        null === $character->getName() ||
        null === $character->getSurname() ||
        null === $character->getSlug() ||
        null === $character->getIdentifier() ||
        null === $character->getCreation() ||
        null === $character->getUpdatedAt()
        ) {
            $errorMsg = 'Missing data for Entity -> ' . json_encode($character->toArray());
            throw new UnprocessableEntityHttpException($errorMsg);
        }
    }
}