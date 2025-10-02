<?php

namespace App\Hydrators;

use App\Dto\MotisApi\LegDto;
use App\Dto\MotisApi\StopPlaceDto;
use App\Dto\MotisApi\TripDto;
use App\Models\TransportTrip;
use App\Models\TransportTripStop;

class DbTripHydrator
{
    public function hydrateStopPlace(TransportTripStop $stop): StopPlaceDto
    {
        return new StopPlaceDto()
            ->setName($stop->location->name)
            ->setStopId($stop->location->id)
            ->setLatitude((float) $stop->location->latitude)
            ->setLongitude((float) $stop->location->longitude)
            ->setArrival($stop->arrival_delay ? $stop->arrival_time?->addSeconds($stop->arrival_delay) : $stop->arrival_time)
            ->setDeparture($stop->departure_delay ? $stop->departure_time?->addSeconds($stop->departure_delay) : $stop->departure_time)
            ->setScheduledArrival($stop->arrival_time)
            ->setScheduledDeparture($stop->departure_time);
    }

    public function hydrateTrip(TransportTrip $trip): TripDto
    {
        $leg = $this->hydrateLeg($trip);

        return new TripDto()
            ->setDuration(0)
            ->setStartTime($leg->startTime)
            ->setEndTime($leg->endTime)
            ->setTransfers(0)
            ->setLegs([$leg]);
    }

    public function hydrateLeg(TransportTrip $trip): LegDto
    {
        $stops = [];
        foreach ($trip->stops as $stop) {
            $stops[] = $this->hydrateStopPlace($stop);
        }

        /** @var StopPlaceDto $origin */
        $origin = array_shift($stops);
        /** @var StopPlaceDto $destination */
        $destination = array_pop($stops);

        return new LegDto()
            ->setDuration(0)
            ->setStartTime($origin->departure)
            ->setEndTime($destination->arrival)
            ->setRealTime(false)
            ->setScheduledStartTime($origin->scheduledDeparture)
            ->setScheduledEndTime($destination->scheduledArrival)
            ->setMode($trip->mode)
            ->setFrom($origin)
            ->setTo($destination)
            ->setHeadSign($destination->name)
            ->setAgencyName(null)
            ->setAgencyId(null)
            ->setTripId($trip->foreign_trip_id)
            ->setRouteShortName($trip->line_name ?? '')
            ->setRouteLongName($trip->route_long_name ?? '')
            ->setTripShortName($trip->trip_short_name ?? '')
            ->setDisplayName($trip->display_name ?? '')
            ->setSource($trip->provider)
            ->setIntermediateStops($stops);

    }
}
