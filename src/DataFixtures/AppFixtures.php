<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Character;
use App\Entity\Building;
use App\Entity\User;

class AppFixtures extends Fixture
{
    public function __construct(
        private SluggerInterface $slugger,
        private UserPasswordHasherInterface $hasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {

        // Creates All the Characters from json
        $characters = json_decode(file_get_contents('https://la-guilde-des-seigneurs.com/json/characters.json'), true);
        $buildings = json_decode(file_get_contents('https://la-guilde-des-seigneurs.com/json/buildings.json'), true);

        // Creates Users
        $emails = [
            'contact@example.com',
            'info@example.com',
            'email@example.com',
        ];
        $users = [];
        foreach ($emails as $email) {
            $user = new User();
            $user->setEmail($email);
            $user->setPassword($this->hasher->hashPassword($user, 'StrongPassword*'));
            $user->setCreation(new \DateTime());
            $user->setModification(new \DateTime());
            // On définit seulement cet utilisateur comme admin
            if ('contact@example.com' === $email) {
                $user->setRoles(['ROLE_ADMIN']);
            }
            $manager->persist($user);
            $users[] = $user;
        }

        $charactersArray = [];
        foreach ($characters as $characterData) {
            $character = $this->setCharacter($characterData);
            $character->setUser($users[array_rand($users)]);
            $manager->persist($character);
            $charactersArray[] = $character;
        }

        foreach ($buildings as $buildingData) {
            $building = $this->setBuilding($buildingData);
            // Characters
            foreach ($charactersArray as $character) {
                if ($building->getCaste() === $character->getCaste()) {
                    $building->addCharacter($character);
                }
            }
            $manager->persist($building);
        }

        $manager->flush();
    }

    // Sets the Character with its data
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
        $character->setModification(new \DateTime());

        return $character;
    }

    // Sets the Character with its data
    public function setBuilding(array $buildingData): Building
    {
        $building = new Building();

        foreach ($buildingData as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($building, $method)) {
                $building->$method($value ?? null);
            }
        }
        $building->setSlug($this->slugger->slug($buildingData['name'])->lower());
        $building->setIdentifier(hash('sha1', uniqid()));
        $building->setCreation(new \DateTime());
        $building->setModification(new \DateTime());

        if (!isset($buildingData['rating'])) {
            $building->setRating(0);
        }

        return $building;
    }
}
