<?php

namespace App\Dto\MotisApi;

use App\Enums\TransportMode;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'StopTimeDto',
    description: 'Data Transfer Object for a Stop Time in the Motis API',
    required: ['place', 'mode', 'realTime', 'headSign', 'agencyName', 'agencyId', 'tripId', 'routeShortName', 'routeLongName', 'tripShortName', 'routeColor', 'routeTextColor', 'displayName', 'source'],
    type: 'object'
)]
class StopTimeDto
{
    #[OA\Property(
        property: 'place',
        ref: StopPlaceDto::class,
        description: 'Stop place information for this stop time'
    )]
    public StopPlaceDto $place;

    #[OA\Property(
        property: 'mode',
        ref: TransportMode::class,
        description: 'Transport mode for the stop time (e.g., bus, train, tram)',
        type: 'enum'
    )]
    public string $mode;

    #[OA\Property(
        property: 'realTime',
        description: 'Indicates if the stop time is based on real-time data',
        type: 'boolean'
    )]
    public bool $realTime;

    #[OA\Property(
        property: 'headSign',
        description: 'Head sign for the stop time, if available',
        type: 'string'
    )]
    public string $headSign;

    #[OA\Property(
        property: 'agencyName',
        description: 'Name of the agency associated with this stop time, if available',
        type: 'string',
        nullable: true
    )]
    public ?string $agencyName = null;

    #[OA\Property(
        property: 'agencyId',
        description: 'Identifier for the agency associated with this stop time, if available',
        type: 'string',
        nullable: true
    )]
    public ?string $agencyId = null;

    #[OA\Property(
        property: 'tripId',
        description: 'Identifier for the trip associated with this stop time',
        type: 'string'
    )]
    public string $tripId;

    #[OA\Property(
        property: 'routeShortName',
        description: 'Short name for the route, if available',
        type: 'string'
    )]
    public string $routeShortName;

    #[OA\Property(
        property: 'routeLongName',
        description: 'Long name for the route, if available',
        type: 'string',
        nullable: true
    )]
    public ?string $routeLongName = null;

    #[OA\Property(
        property: 'tripShortName',
        description: 'Short name for the trip, if available',
        type: 'string',
        nullable: true
    )]
    public ?string $tripShortName = null;

    #[OA\Property(
        property: 'routeColor',
        description: 'Color for the route, if available',
        type: 'string',
        nullable: true
    )]
    public ?string $routeColor = null;

    #[OA\Property(
        property: 'routeTextColor',
        description: 'Text color for the route, if available',
        type: 'string',
        nullable: true
    )]
    public ?string $routeTextColor = null;

    #[OA\Property(
        property: 'displayName',
        description: 'Display name for the stop time, if available',
        type: 'string',
        nullable: true
    )]
    public ?string $displayName;

    #[OA\Property(
        property: 'source',
        description: 'Source of the stop time information',
        type: 'string'
    )]
    public string $source;

    public function setRouteColor(?string $routeColor): StopTimeDto
    {
        $this->routeColor = $routeColor;

        return $this;
    }

    public function setRouteTextColor(?string $routeTextColor): StopTimeDto
    {
        $this->routeTextColor = $routeTextColor;

        return $this;
    }

    public function setDisplayName(?string $displayName): StopTimeDto
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function setPlace(StopPlaceDto $place): StopTimeDto
    {
        $this->place = $place;

        return $this;
    }

    public function setMode(string $mode): StopTimeDto
    {
        $this->mode = $mode;

        return $this;
    }

    public function setRealTime(bool $realTime): StopTimeDto
    {
        $this->realTime = $realTime;

        return $this;
    }

    public function setHeadSign(string $headSign): StopTimeDto
    {
        $this->headSign = $headSign;

        return $this;
    }

    public function setAgencyName(?string $agencyName): StopTimeDto
    {
        $this->agencyName = $agencyName;

        return $this;
    }

    public function setAgencyId(?string $agencyId): StopTimeDto
    {
        $this->agencyId = $agencyId;

        return $this;
    }

    public function setTripId(string $tripId): StopTimeDto
    {
        $this->tripId = $tripId;

        return $this;
    }

    public function setRouteShortName(string $routeShortName): StopTimeDto
    {
        $this->routeShortName = $routeShortName;

        return $this;
    }

    public function setSource(string $source): StopTimeDto
    {
        $this->source = $source;

        return $this;
    }

    public function setRouteLongName(?string $routeLongName): StopTimeDto
    {
        $this->routeLongName = $routeLongName;

        return $this;
    }

    public function setTripShortName(?string $tripShortName): StopTimeDto
    {
        $this->tripShortName = $tripShortName;

        return $this;
    }
}
