<?php

namespace App\Listener;

use App\Event\BuildingEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BuildingListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        // Événements que l'on souhaite écouter
        return array(
            BuildingEvent::BUILDING_UPDATED => 'buildingUpdated',
        );
    }

    // Méthode appelée lorsque l'objet est modifié
    public function buildingUpdated($event)
    {
        // Réception de l'objet Character avec le getter
        $character = $event->getCharacter();

        $character->setStrength($character->getStrength() - 20);
    }
}
