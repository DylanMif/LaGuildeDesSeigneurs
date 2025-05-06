<?php

namespace App\Controller;

use App\Entity\Building;
use App\Service\BuildingServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

final class BuildingController extends AbstractController
{
    public function __construct(
        private BuildingServiceInterface $buildingService
    ) {}

    // INDEX
    #[
        Route('/buildings',
        name: 'app_building_index',
        methods: ['GET'])
    ]
    public function index(): JsonResponse
    {
        $this->denyAccessUnlessGranted('buildingIndex', null);
        $buildings = $this->buildingService->findAll();
        return new JsonResponse($buildings);
    }

    #[
        Route(
            path: "/buildings/{identifier:building}",
            requirements: ["identifier" => "^([a-z0-9]{40})$"],
            name: "app_building_display",
            methods: ["GET"]
        )
    ]
    public function display(Building $building): JsonResponse
    {
        $this->denyAccessUnlessGranted('buildingDisplay', $building);
        return new JsonResponse($building->toArray());
    }

    #[Route('/buildings', name: 'app_buildings_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('buildingCreate', null);
        $buildings = $this->buildingService->create($request->getContent());
        return new JsonResponse($buildings->toArray(), JsonResponse::HTTP_CREATED);
    }

    // UPDATE
    #[
        Route('/buildings/{identifier:building}',
        requirements: ['identifier' => '^([a-z0-9]{40})$'],
        name: 'app_building_update',
        methods: ['PUT'])
    ]
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
    public function delete(Building $building): JsonResponse
    {
        $this->denyAccessUnlessGranted('buildingDelete', $building);
        $this->buildingService->delete($building);
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
