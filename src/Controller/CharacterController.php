<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Character;

final class CharacterController extends AbstractController
{
    #[Route('/characters', name: 'app_character_display')]
    public function display(): JsonResponse
    {
        $character = new Character();
        dump($character);
        dd($character->toArray());

        return new JsonResponse($character->toArray());
    }
}
