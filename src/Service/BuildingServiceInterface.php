<?php

namespace App\Service;

use App\Entity\Building;

interface BuildingServiceInterface
{
    public function findAll();

    public function create();
    public function update(Building $building);
    public function delete(Building $building);
}