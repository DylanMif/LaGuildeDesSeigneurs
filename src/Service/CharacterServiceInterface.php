<?php

namespace App\Service;

use App\Entity\Character;

interface CharacterServiceInterface
{
    // Creates the character
    public function create();

    public function findAll();
}