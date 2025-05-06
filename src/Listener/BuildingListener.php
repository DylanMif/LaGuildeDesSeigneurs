<?php

namespace App\Listener;
use App\Event\BuildingEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
class BuildingListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return array(
            BuildingEvent::BUILDING_UPDATED => 'buildingUpdated',
        );
    }
    public function buildingUpdated($event)
    {
        $building = $event->getBuilding();
        $building->setStrength($building->getStrength() - 20);
    }
}