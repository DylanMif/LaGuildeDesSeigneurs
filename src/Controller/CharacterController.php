<?php

namespace App\Controller;

use App\Service\CharacterServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Character;

final class CharacterController extends AbstractController
{
    public function __construct(
        private CharacterServiceInterface $characterService
    ) {}

    #[
        Route(
            "/characters/{identifier:character}",
            requirements: ["identifier" => "^([a-z0-9]{40})$"],
            name: "app_character_display",
            methods: ["GET"]
        )
    ]
    public function display(Character $character): JsonResponse
    {
        return new JsonResponse($character->toArray());
    }

    #[Route('/characters', name: 'app_character_create', methods: ['POST'])]
    public function create(): JsonResponse
    {
        $character = $this->characterService->create();
        return new JsonResponse($character->toArray(), JsonResponse::HTTP_CREATED);
    }
}
