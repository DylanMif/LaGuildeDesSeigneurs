<?php

namespace App\Event;

use App\Entity\Character;
use Symfony\Contracts\EventDispatcher\Event;

class CharacterEvent extends Event
{
    // Constante pour le nom de l'event, nommage par convention
    public const CHARACTER_CREATED = 'app.character.created';
    public const CHARACTER_UPDATED = 'app.character.updated';
    public const CHARACTER_CREATED_POST_DATABASE = 'app.character.created.post.database';

    // Injection de l'objet
    public function __construct(
        protected Character $character
    ) {
    }

    // Getter pour l'objet
    public function getCharacter(): Character
    {
        return $this->character;
    }
}
