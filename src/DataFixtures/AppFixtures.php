<?php

namespace App\DataFixtures;

use App\Repository\BuildingRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Character;
use App\Entity\Building;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;

class AppFixtures extends Fixture
{
    public function __construct(
        private SluggerInterface $slugger,
        private BuildingRepository $buildingRepository,
        private UserPasswordHasherInterface $hasher
    ){}
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $buildings = json_decode(file_get_contents('https://la-guilde-des-seigneurs.com/json/buildings.json'), true);
        foreach ($buildings as $buildingData) {
            $manager->persist($this->setBuilding($buildingData));
        }
        $manager->flush();
        
        $dbBuildings = $this->buildingRepository->findAll();

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

        $characters = json_decode(file_get_contents('https://la-guilde-des-seigneurs.com/json/characters.json'), true);
        foreach ($characters as $characterData) {
                $manager->persist($this->setCharacter($characterData, 
                $dbBuildings[rand(0, count($dbBuildings)-1)],
                $users[array_rand($users)]
            ));
        }
        $manager->flush();
    }

    public function setCharacter(array $characterData, Building $building, User $user): Character
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
        $character->setUser($user);
        $character->setUpdatedAt(new \DateTimeImmutable());
        $character->setBuilding($building);
        return $character;
    }

    public function setBuilding(array $buildingData): Building
    {
        $building = new Building();
        foreach ($buildingData as $key => $value) {
        $method = 'set' . ucfirst($key); // Construit le nom de la méthode
        if (method_exists($building, $method)) { // Si la méthode existe
        $building->$method($value ?? null); // Appelle la méthode
        }
        }
        $building->setSlug($this->slugger->slug($buildingData['name'])->lower());
        $building->setPrice(50);
        $building->setIdentifier(hash('sha1', uniqid()));
        return $building;
    }
}
