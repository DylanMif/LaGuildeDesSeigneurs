<?php

namespace App\Event;
use App\Entity\Character;
use Symfony\Contracts\EventDispatcher\Event;
class CharacterEvent extends Event
{
    // Constante pour le nom de l'event, nommage par convention
    public const CHARACTER_CREATED = 'app.character.created';
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