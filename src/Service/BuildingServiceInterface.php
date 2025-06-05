<?php

namespace App\Service;

use App\Entity\Building;

interface BuildingServiceInterface
{
    // Creates the building with provided data
    public function create(string $data);

    // Finds all the buildings
    public function findAll();

    // Updates the building with provided data
    public function update(Building $building, string $data);

    // Deletes the building
    public function delete(Building $building);

    // Checks if the entity has been well filled
    public function isEntityFilled(Building $building);

    // Submits the data to hydrate the object
    public function submit(Building $building, $formName, $data);

    // Serializes the object(s)
    public function serializeJson($object);

    // Finds all the characters paginated
    public function findAllPaginated($query);

    // Defines the links for HATEOAS
    public function setLinks($object);

    // Gets random images
    public function getImages(int $number);
}
