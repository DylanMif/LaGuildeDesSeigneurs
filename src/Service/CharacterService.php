<?php
namespace App\Service;

use App\Repository\CharacterRepository;
use DateTime; // on ajoute le use pour supprimer le \ dans setCreation()
use App\Entity\Character;
use Doctrine\ORM\EntityManagerInterface;
class CharacterService implements CharacterServiceInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private CharacterRepository $characterRepository
    ) {
    }
    // Creates the character
    public function create(): Character
    {
        $character = new Character();
        $character->setKind('Dame');
        $character->setName('Maeglin');
        $character->setSlug('maeglin');
        $character->setSurname('Oeil vif');
        $character->setCaste('Archer');
        $character->setKnowledge('Nombres');
        $character->setIntelligence(120);
        $character->setStrength(120);
        $character->setIdentifier(hash('sha1', uniqid()));
        $character->setImage('/dames/maeglin.webp');
        $character->setCreation(new DateTime());

        $this->em->persist($character);
        $this->em->flush();
        return $character;
    }
}