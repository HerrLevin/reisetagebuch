<?php

namespace App\Hydrators;

use App\Dto\MotisApi\AreaDto;
use App\Dto\MotisApi\GeocodeResponseEntry;
use App\Dto\MotisApi\LegDto;
use App\Dto\MotisApi\LocationType;
use App\Dto\MotisApi\StopDto;
use App\Dto\MotisApi\StopPlaceDto;
use App\Dto\MotisApi\StopTimeDto;
use App\Dto\MotisApi\TripDto;
use Carbon\Carbon;

class MotisHydrator
{
    public function hydrateStop(array $data, ?float $distance = null): StopDto
    {
        $distance = (int) $distance;

        return new StopDto()
            ->setStopId($data['stopId'])
            ->setName($data['name'])
            ->setLatitude((float) $data['lat'])
            ->setLongitude((float) $data['lon'])
            ->setDistance($data['distance'] ?? $distance ?? null);
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
            ->setDisplayName($data['displayName'] ?? null)
            ->setRouteLongName($data['routeLongName'] ?? null)
            ->setTripShortName($data['tripShortName'] ?? null)
            ->setSource($data['source']);

    }

    public function hydrateStopPlace(array $data): StopPlaceDto
    {
        return new StopPlaceDto()
            ->setName($data['name'])
            ->setStopId($data['stopId'])
            ->setLatitude((float) $data['lat'])
            ->setLongitude((float) $data['lon'])
            ->setArrival(! empty($data['arrival']) ? Carbon::parse($data['arrival']) : null)
            ->setDeparture(! empty($data['departure']) ? Carbon::parse($data['departure']) : null)
            ->setScheduledArrival(! empty($data['scheduledArrival']) ? Carbon::parse($data['scheduledArrival']) : null)
            ->setScheduledDeparture(! empty($data['scheduledDeparture']) ? Carbon::parse($data['scheduledDeparture']) : null);
    }

    public function hydrateTrip(array $data): TripDto
    {
        $legs = [];
        foreach ($data['legs'] as $leg) {
            $legs[] = $this->hydrateLeg($leg);
        }

        return new TripDto()
            ->setDuration($data['duration'])
            ->setStartTime(! empty($data['startTime']) ? Carbon::parse($data['startTime']) : null)
            ->setEndTime(! empty($data['endTime']) ? Carbon::parse($data['endTime']) : null)
            ->setTransfers($data['transfers'])
            ->setLegs($legs);
    }

    public function hydrateLeg(array $data): LegDto
    {
        $intermediateStops = [];
        foreach ($data['intermediateStops'] as $stop) {
            $intermediateStops[] = $this->hydrateStopPlace($stop);
        }

        $shortName = null;
        if (isset($data['tripShortName'])) {
            $shortName = $data['tripShortName'];
            if (is_numeric($shortName)) {
                $shortName = (string) (int) $shortName; // remove leading zeros
            }
        }

        return new LegDto()
            ->setDuration($data['duration'])
            ->setStartTime(! empty($data['startTime']) ? Carbon::parse($data['startTime']) : null)
            ->setEndTime(! empty($data['endTime']) ? Carbon::parse($data['endTime']) : null)
            ->setRealTime($data['realTime'])
            ->setScheduledStartTime(! empty($data['scheduledStartTime']) ? Carbon::parse($data['scheduledStartTime']) : null)
            ->setScheduledEndTime(! empty($data['scheduledEndTime']) ? Carbon::parse($data['scheduledEndTime']) : null)
            ->setMode($data['mode'])
            ->setFrom($this->hydrateStopPlace($data['from']))
            ->setTo($this->hydrateStopPlace($data['to']))
            ->setHeadSign($data['headsign'])
            ->setAgencyName($data['agencyName'] ?? null)
            ->setAgencyId($data['agencyId'] ?? null)
            ->setTripId($data['tripId'])
            ->setRouteShortName($data['routeShortName'])
            ->setRouteLongName($data['routeLongName'] ?? null)
            ->setTripShortName($shortName)
            ->setDisplayName($data['displayName'] ?? null)
            ->setSource($data['source'])
            ->setIntermediateStops($intermediateStops);

    }

    public function hydrateArea(array $data): AreaDto
    {
        return new AreaDto()
            ->setName($data['name'])
            ->setDefault($data['default'] ?? null)
            ->setAdminLevel($data['adminLevel'] ?? null)
            ->setMatched($data['matched'] ?? false)
            ->setUnique($data['unique'] ?? false);
    }

    public function hydrateGeocodeEntry(array $data): GeocodeResponseEntry
    {
        $areas = [];

        if (isset($data['areas']) && is_array($data['areas'])) {
            foreach ($data['areas'] as $area) {
                $areas[] = $this->hydrateArea($area);
            }
        }

        return new GeocodeResponseEntry()
            ->setType(LocationType::tryFrom($data['type']))
            ->setTokens($data['tokens'])
            ->setName($data['name'])
            ->setIdentifier($data['id'])
            ->setLat((float) $data['lat'])
            ->setLon((float) $data['lon'])
            ->setLevel($data['level'] ?? null)
            ->setStreet($data['street'] ?? null)
            ->setHouseNumber($data['houseNumber'] ?? null)
            ->setZip($data['zip'] ?? null)
            ->setAreas($areas)
            ->setScore((float) $data['score']);
    }
}
