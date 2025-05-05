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

    // INDEX
    #[
        Route('/characters',
        name: 'app_character_index',
        methods: ['GET'])
    ]
    public function index(): JsonResponse
    {
        $this->denyAccessUnlessGranted('characterIndex', null);
        $characters = $this->characterService->findAll();
        return new JsonResponse($characters);
    }

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
        $this->denyAccessUnlessGranted('characterDisplay', $character);
        return new JsonResponse($character->toArray());
    }

    #[Route('/characters', name: 'app_character_create', methods: ['POST'])]
    public function create(): JsonResponse
    {
        $this->denyAccessUnlessGranted('characterCreate', null);
        $character = $this->characterService->create();
        return new JsonResponse($character->toArray(), JsonResponse::HTTP_CREATED);
    }

    // UPDATE
    #[
        Route('/characters/{identifier:character}',
        requirements: ['identifier' => '^([a-z0-9]{40})$'],
        name: 'app_character_update',
        methods: ['PUT'])
    ]
    public function update(Character $character): JsonResponse
    {
        $this->denyAccessUnlessGranted('characterUpdate', $character);
        $this->characterService->update($character);
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
