<?php

declare(strict_types=1);

namespace App\Hydrators;

use App\Dto\MotisApi\StopPlaceDto;
use App\Models\Location;
use App\Models\TransportTripStop;

class TripDtoHydrator
{
    public function hydrateStopPlace(TransportTripStop $stop, ?Location $location = null): StopPlaceDto
    {
        $location = $location ?? $stop->location;

        return new StopPlaceDto()
            ->setName($location->name)
            ->setStopId($location->id)
            ->setLatitude($location->location->getLatitude())
            ->setLongitude($location->location->getLongitude())
            ->setCancelled($stop->cancelled)
            ->setArrival(is_null($stop->arrival_delay) ? null : $stop->arrival_time->addSeconds($stop->arrival_delay))
            ->setDeparture(is_null($stop->departure_delay) ? null : $stop->departure_time->addSeconds($stop->departure_delay))
            ->setScheduledArrival($stop->arrival_time)
            ->setScheduledDeparture($stop->departure_time);
    }
}
