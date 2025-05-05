<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Character;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private SluggerInterface $slugger,
    ){}
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $characters = json_decode(file_get_contents('https://la-guilde-des-seigneurs.com/json/characters.json'), true);
        foreach ($characters as $characterData) {
            $manager->persist($this->setCharacter($characterData));
        }
        $manager->flush();
    }

    public function setCharacter(array $characterData): Character
    {
        $character = new Character();
        foreach ($characterData as $key => $value) {
        $method = 'set' . ucfirst($key); // Construit le nom de la méthode
        if (method_exists($character, $method)) { // Si la méthode existe
        $character->$method($value ?? null); // Appelle la méthode
        }
        }
        $character->setSlug($this->slugger->slug($characterData['name'])->lower());
        $character->setIdentifier(hash('sha1', uniqid()));
        $character->setCreation(new \DateTime());
        return $character;
    }
}
