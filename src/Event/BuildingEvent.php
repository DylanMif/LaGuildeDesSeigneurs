<?php

namespace App\Event;

use App\Entity\Building;
use Symfony\Contracts\EventDispatcher\Event;

class BuildingEvent extends Event
{
    // Constante pour le nom de l'event, nommage par convention
    public const BUILDING_CREATED = 'app.building.created';
    public const BUILDING_UPDATED = 'app.building.updated';
    public const BUILDING_CREATED_POST_DATABASE = 'app.building.created.post.database';

    // Injection de l'objet
    public function __construct(
        protected Building $building
    ) {
    }

    // Getter pour l'objet
    public function getCharacter(): Building
    {
        return $this->building;
    }
}
