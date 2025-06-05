<?php

namespace App\Service;

use App\Entity\Character;

interface CharacterServiceInterface
{
    // Creates the character
    public function create(string $data);

    // Finds all the characters
    public function findAll();

    // Updates the character
    public function update(Character $character, string $data);

    // Updates the character
    public function delete(Character $character);

    // Checks if the entity has been well filled
    public function isEntityFilled(Character $character);

    // Submits the data to hydrate the object
    public function submit(Character $character, $formName, $data);

    // Serializes the object(s)
    public function serializeJson($object);

    // Finds all the characters paginated
    public function findAllPaginated($query);

    // Defines the links for HATEOAS
    public function setLinks($object);

    // Gets random images
    public function getImages(int $number, ?string $kind = null);

}
