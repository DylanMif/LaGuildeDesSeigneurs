<?php

namespace App\Controller;

use App\Service\CharacterServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Character;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

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
        return JsonResponse::fromJsonString($this->characterService->serializeJson($characters));
    }

    #[
        Route(
            '/characters/{identifier}',
            requirements: ["identifier" => "^([a-z0-9]{40})$"],
            name: "app_character_display",
            methods: ["GET"]
        )
    ]
    public function display(
        #[MapEntity(expr: 'repository.findOneByIdentifier(identifier)')]
        Character $character
    ): JsonResponse
    {
        $this->denyAccessUnlessGranted('characterDisplay', $character);
        return JsonResponse::fromJsonString($this->characterService->serializeJson($character));
    }

    #[Route('/characters', name: 'app_character_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('characterCreate', null);
        $character = $this->characterService->create($request->getContent());
        $response = JsonResponse::fromJsonString($this->characterService->serializeJson($character), JsonResponse::HTTP_CREATED);
        $url = $this->generateUrl(
        'app_character_display',
        ['identifier' => $character->getIdentifier()]
        );
        $response->headers->set('Location', $url);
        return $response;
    }

    // UPDATE
    #[
        Route('/characters/{identifier:character}',
        requirements: ['identifier' => '^([a-z0-9]{40})$'],
        name: 'app_character_update',
        methods: ['PUT'])
    ]
    public function update(Request $request, Character $character): JsonResponse
    {
        $this->denyAccessUnlessGranted('characterUpdate', $character);
        $this->characterService->update($character, $request->getContent());
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    // DELETE
    #[
        Route('/characters/{identifier:character}',
        requirements: ['identifier' => '^([a-z0-9]{40})$'],
        name: 'app_character_delete',
        methods: ['DELETE'])
    ]
    public function delete(Character $character): JsonResponse
    {
        $this->denyAccessUnlessGranted('characterDelete', $character);
        $this->characterService->delete($character);
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
