<?php

namespace App\Service;

use App\Entity\Character;

interface CharacterServiceInterface
{
    // Creates the character
    public function create(string $data);

    public function findAll();
    public function update(Character $character, string $data);
    public function delete(Character $character);
    public function isEntityFilled(Character $character);
    public function submit(Character $character, $formName, $data);
    public function serializeJson($object);
    public function findAllPaginated($query);
    public function setLinks($object);
}