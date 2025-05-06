<?php

namespace App\Controller;

use App\Entity\Building;
use App\Service\BuildingServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

final class BuildingController extends AbstractController
{
    public function __construct(
        private BuildingServiceInterface $buildingService
    ) {}

    // INDEX
    #[OA\Response(
        response: 200,
        description: 'Returns an array of Buildings',
        content: new OA\JsonContent(
        type: 'array',
        items: new OA\Items(ref: new Model(type: Building::class))
        )
    )]
    #[OA\Response(
        response: 403,
        description: 'Access denied'
    )]
    #[OA\Tag(name: 'Building')]
    #[
        Route('/buildings',
        name: 'app_building_index',
        methods: ['GET'])
    ]
    public function index(): JsonResponse
    {
        $this->denyAccessUnlessGranted('buildingIndex', null);
        $buildings = $this->buildingService->findAll();
        return JsonResponse::fromJsonString($this->buildingService->serializeJson($buildings));
    }

    #[
        Route(
            path: "/buildings/{identifier}",
            requirements: ["identifier" => "^([a-z0-9]{40})$"],
            name: "app_building_display",
            methods: ["GET"]
        )
    ]
    #[OA\Parameter(
        name: 'identifier',
        in: 'path',
        description: 'Identifier for the Building',
        schema: new OA\Schema(type: 'string'),
        required: true
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the Building',
        content: new Model(type: Building::class)
    )]
    #[OA\Response(
        response: 403,
        description: 'Access denied'
    )]
    #[OA\Response(
        response: 404,
        description: 'Not found'
    )]
    #[OA\Tag(name: 'Building')]
    public function display(
        #[MapEntity(expr: 'repository.findOneByIdentifier(identifier)')]
        Building $building
    ): JsonResponse
    {
        $this->denyAccessUnlessGranted('buildingDisplay', $building);
        return JsonResponse::fromJsonString($this->buildingService->serializeJson($building));
    }

    #[OA\RequestBody(
        request: "Building",
        description: "Data for the Building",
        required: true,
        content: new OA\JsonContent(
        type: Building::class,
        example: [
        "name" => "Château Silken",
        "caste" => "Archer",
        "image" => "/buildings/chateau-silken.webp",
        "strength" => 1200
        ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Returns the Building',
        content: new Model(type: Building::class)
    )]
    #[OA\Response(
        response: 403,
        description: 'Access denied'
    )]
    #[OA\Tag(name: 'Building')]
    #[Route('/buildings', name: 'app_buildings_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('buildingCreate', null);
        $building = $this->buildingService->create($request->getContent());
        $response = new JsonResponse($building->toArray(), JsonResponse::HTTP_CREATED);
        $response = JsonResponse::fromJsonString($this->buildingService->serializeJson($building), JsonResponse::HTTP_CREATED);
        $url = $this->generateUrl(
        'app_building_display',
        ['identifier' => $building->getIdentifier()]
        );
        $response->headers->set('Location', $url);
        return $response;
    }

    // UPDATE
    #[
        Route('/buildings/{identifier:building}',
        requirements: ['identifier' => '^([a-z0-9]{40})$'],
        name: 'app_building_update',
        methods: ['PUT'])
    ]
    #[OA\Parameter(
        name: 'identifier',
        in: 'path',
        description: 'Identifier for the Building',
        schema: new OA\Schema(type: 'string'),
        required: true
        )]
        #[OA\RequestBody(
        request: "Building",
        description: "Data for the Building",
        required: true,
        content: new OA\JsonContent(
        type: Building::class,
        example: [
        "name" => "Château Oakenfield",
        "caste" => "Erudit",
        ]
        )
    )]
    #[OA\Response(
        response: 204,
        description: 'No content'
    )]
    #[OA\Response(
        response: 403,
        description: 'Access denied'
    )]
    #[OA\Response(
        response: 404,
        description: 'Not found'
    )]
    #[OA\Tag(name: 'Building')]
    public function update(Request $request, Building $building): JsonResponse
    {
        $this->denyAccessUnlessGranted('buildingUpdate', $building);
        $building = $this->buildingService->update($building, $request->getContent());
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    // DELETE
    #[
        Route('/buildings/{identifier:building}',
        requirements: ['identifier' => '^([a-z0-9]{40})$'],
        name: 'app_building_delete',
        methods: ['DELETE'])
    ]
    #[OA\Parameter(
        name: 'identifier',
        in: 'path',
        description: 'Identifier for the Building',
        schema: new OA\Schema(type: 'string'),
        required: true
    )]
    #[OA\Response(
        response: 204,
        description: 'No content'
    )]
    #[OA\Response(
        response: 403,
        description: 'Access denied'
    )]
    #[OA\Response(
        response: 404,
        description: 'Not found'
    )]
    #[OA\Tag(name: 'Building')]
    public function delete(Building $building): JsonResponse
    {
        $this->denyAccessUnlessGranted('buildingDelete', $building);
        $this->buildingService->delete($building);
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
