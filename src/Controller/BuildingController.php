<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use App\Entity\Building;
use App\Service\BuildingServiceInterface;

final class BuildingController extends AbstractController
{
    public function __construct(
        private BuildingServiceInterface $buildingService
    ) {
    }

    // INDEX
    #[
        Route(
            '/buildings/',
            name: 'app_building_index',
            methods: ['GET']
        )
    ]
    #[Cache(public: true, maxage: 3600, mustRevalidate: true)]
    #[OA\Parameter(
        name: 'page',
        in: 'query',
        description: 'Number of the page',
        schema: new OA\Schema(type: 'integer', default: 1),
        required: true
    )]
    #[OA\Parameter(
        name: 'size',
        in: 'query',
        description: 'Number of records',
        schema: new OA\Schema(type: 'integer', default: 10, minimum: 1, maximum: 100),
        required: true
    )]
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
    public function index(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('buildingIndex', null);
        
        $buildings = $this->buildingService->findAllPaginated($request->query);

        return JsonResponse::fromJsonString($this->buildingService->serializeJson($buildings));
    }

    // Display
    #[
        Route(
            '/buildings/{identifier}',
            requirements: ['identifier' => '^([a-z0-9]{40})$'],
            name: 'app_building_display',
            methods: ['GET']
        )
    ]
    #[Cache(public: true, maxage: 3600, mustRevalidate: true)]
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
        content: new OA\JsonContent(ref: new Model(type: Building::class))
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
    ): JsonResponse {
        $this->denyAccessUnlessGranted('buildingDisplay', $building);

        return JsonResponse::fromJsonString($this->buildingService->serializeJson($building));
    }

    // CREATE
    #[
        Route(
            '/buildings/',
            name: 'app_building_create',
            methods: ['POST']
        )]
    #[OA\RequestBody(
        request: "Building",
        description: "Data for the Building",
        required: true,
        content: new OA\JsonContent(
            type: Building::class,
            example: [
            "name" => "Tour de guet",
            "level" => 3,
            "type" => "Défense",
            "health" => 1500,
            "cost" => 1200,
            "buildTime" => 3600,
            "image" => "/buildings/tower.webp"
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
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('buildingCreate', null);

        $building = $this->buildingService->create($request->getContent());
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
        Route(
            '/buildings/{identifier:building}',
            requirements: ['identifier' => '^([a-z0-9]{40})$'],
            name: 'app_building_update',
            methods: ['PUT']
        )
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
            "name" => "Tour de défense",
            "level" => 4,
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
        $this->buildingService->update($building, $request->getContent());
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    // DELETE
    #[
        Route(
            '/buildings/{identifier:building}',
            requirements: ['identifier' => '^([a-z0-9]{40})$'],
            name: 'app_building_delete',
            methods: ['DELETE']
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

    // IMAGES
    #[Route(
        '/buildings/images/{number}',
        name: 'app_building_images',
        requirements: ['number' => '^([0-9]{1,2})$'],
        methods: ['GET']
    )]
    #[OA\Parameter(
        name: 'number',
        in: 'path',
        description: 'Number of images',
        schema: new OA\Schema(type: 'integer'),
        required: false
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns links for images'
    )]
    #[OA\Response(
        response: 403,
        description: 'Access denied'
    )]
    #[OA\Tag(name: 'Building')]
    public function images(int $number = 1): JsonResponse
    {
        $this->denyAccessUnlessGranted('characterIndex', null);

        $images = $this->buildingService->getImages($number);

        return new JsonResponse($images);
    }
}
