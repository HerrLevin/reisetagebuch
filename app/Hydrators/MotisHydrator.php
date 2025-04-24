<?php

namespace App\Hydrators;

use App\Dto\MotisApi\LegDto;
use App\Dto\MotisApi\StopDto;
use App\Dto\MotisApi\StopPlaceDto;
use App\Dto\MotisApi\StopTimeDto;
use App\Dto\MotisApi\TripDto;
use Carbon\Carbon;

class MotisHydrator
{
    public function hydrateStop(array $data, ?float $distance = null): StopDto
    {
        return new StopDto(
            $data['stopId'],
            $data['name'],
            (float) $data['lat'],
            (float) $data['lon'],
            $distance
        );
    }

    public function hydrateStopTime(array $data): StopTimeDto
    {
        $place = $this->hydrateStopPlace($data['place']);

        return new StopTimeDto()
            ->setPlace($place)
            ->setMode($data['mode'])
            ->setRealTime($data['realTime'])
            ->setHeadSign($data['headsign'])
            ->setAgencyName($data['agencyName'] ?? null)
            ->setAgencyId($data['agencyId'] ?? null)
            ->setTripId($data['tripId'])
            ->setRouteShortName($data['routeShortName'])
            ->setSource($data['source']);

    }

    private function hydrateStopPlace(array $data): StopPlaceDto {
        return new StopPlaceDto()
            ->setName($data['name'])
            ->setStopId($data['stopId'])
            ->setLatitude((float) $data['lat'])
            ->setLongitude((float) $data['lon'])
            ->setArrival(!empty($data['arrival']) ? Carbon::parse($data['arrival']) : null)
            ->setDeparture(!empty($data['departure']) ? Carbon::parse($data['departure']) : null)
            ->setScheduledArrival(!empty($data['scheduledArrival']) ? Carbon::parse($data['scheduledArrival']) : null)
            ->setScheduledDeparture(!empty($data['scheduledDeparture']) ? Carbon::parse($data['scheduledDeparture']) : null);
    }

    public function hydrateTrip(array $data): TripDto
    {
        $legs = [];
        foreach ($data['legs'] as $leg) {
            $legs[] = $this->hydrateLeg($leg);
        }

        return new TripDto()
            ->setDuration($data['duration'])
            ->setStartTime(!empty($data['startTime']) ? Carbon::parse($data['startTime']) : null)
            ->setEndTime(!empty($data['endTime']) ? Carbon::parse($data['endTime']) : null)
            ->setTransfers($data['transfers'])
            ->setLegs($legs);
    }

    public function hydrateLeg(array $data) {
        $intermediateStops = [];
        foreach ($data['intermediateStops'] as $stop) {
            $intermediateStops[] = $this->hydrateStopPlace($stop);
        }

        return new LegDto()
            ->setDuration($data['duration'])
            ->setStartTime(!empty($data['startTime']) ? Carbon::parse($data['startTime']) : null)
            ->setEndTime(!empty($data['endTime']) ? Carbon::parse($data['endTime']) : null)
            ->setRealTime($data['realTime'])
            ->setScheduledStartTime(!empty($data['scheduledStartTime']) ? Carbon::parse($data['scheduledStartTime']) : null)
            ->setScheduledEndTime(!empty($data['scheduledEndTime']) ? Carbon::parse($data['scheduledEndTime']) : null)
            ->setMode($data['mode'])
            ->setFrom($this->hydrateStopPlace($data['from']))
            ->setTo($this->hydrateStopPlace($data['to']))
            ->setHeadSign($data['headsign'])
            ->setAgencyName($data['agencyName'] ?? null)
            ->setAgencyId($data['agencyId'] ?? null)
            ->setTripId($data['tripId'])
            ->setRouteShortName($data['routeShortName'])
            ->setSource($data['source'])
            ->setIntermediateStops($intermediateStops);


    }
}
