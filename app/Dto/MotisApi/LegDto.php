<?php

declare(strict_types=1);

namespace App\Dto\MotisApi;

use App\Enums\TransportMode;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LegDto',
    description: 'Data Transfer Object for a Leg of a Trip in the Motis API',
    required: ['mode', 'from', 'to', 'duration', 'startTime', 'endTime', 'scheduledStartTime',
        'scheduledEndTime', 'realTime', 'headSign', 'agencyName', 'agencyId', 'tripId', 'routeShortName',
        'routeLongName', 'tripShortName', 'displayName', 'routeColor', 'routeTextColor', 'source', 'intermediateStops'],
    type: 'object'
)]
class LegDto
{
    #[OA\Property(
        property: 'mode',
        ref: TransportMode::class,
        description: 'Transport mode for the leg (e.g., bus, train, tram)',
        type: 'enum'
    )]
    public string $mode;

    #[OA\Property(
        property: 'from',
        ref: StopPlaceDto::class,
        description: 'Starting stop place for the leg'
    )]
    public StopPlaceDto $from;

    #[OA\Property(
        property: 'to',
        ref: StopPlaceDto::class,
        description: 'Ending stop place for the leg'
    )]
    public StopPlaceDto $to;

    #[OA\Property(
        property: 'duration',
        description: 'Duration of the leg in seconds',
        type: 'integer'
    )]
    public int $duration;

    #[OA\Property(
        property: 'startTime',
        description: 'Start time of the leg, if available',
        type: 'string',
        format: 'date-time',
        nullable: true
    )]
    public ?Carbon $startTime;

    #[OA\Property(
        property: 'endTime',
        description: 'End time of the leg, if available',
        type: 'string',
        format: 'date-time',
        nullable: true
    )]
    public ?Carbon $endTime;

    #[OA\Property(
        property: 'scheduledStartTime',
        description: 'Scheduled start time of the leg, if available',
        type: 'string',
        format: 'date-time',
        nullable: true
    )]
    public ?Carbon $scheduledStartTime;

    #[OA\Property(
        property: 'scheduledEndTime',
        description: 'Scheduled end time of the leg, if available',
        type: 'string',
        format: 'date-time',
        nullable: true
    )]
    public ?Carbon $scheduledEndTime;

    #[OA\Property(
        property: 'realTime',
        description: 'Indicates if the times are real-time or scheduled',
        type: 'boolean'
    )]
    public bool $realTime;

    #[OA\Property(
        property: 'headSign',
        description: 'Head sign for the leg, if available',
        type: 'string'
    )]
    public string $headSign;

    #[OA\Property(
        property: 'agencyName',
        description: 'Name of the agency associated with this leg, if available',
        type: 'string',
        nullable: true
    )]
    public ?string $agencyName;

    #[OA\Property(
        property: 'agencyId',
        description: 'Identifier for the agency associated with this leg, if available',
        type: 'string',
        nullable: true
    )]
    public ?string $agencyId;

    #[OA\Property(
        property: 'tripId',
        description: 'Identifier for the trip associated with this leg',
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
        property: 'displayName',
        description: 'Display name for the leg, if available',
        type: 'string',
        nullable: true
    )]
    public ?string $displayName = null;

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
        property: 'source',
        description: 'Source of the leg information (e.g., "motis", "gtfs")',
        type: 'string'
    )]
    public string $source;

    #[OA\Property(
        property: 'intermediateStops',
        description: 'List of intermediate stops for the leg, if available',
        type: 'array',
        items: new OA\Items(ref: StopPlaceDto::class)
    )]
    /**
     * @var StopPlaceDto[]
     */
    public array $intermediateStops;

    public function setRouteColor(?string $routeColor): LegDto
    {
        $this->routeColor = $routeColor;

        return $this;
    }

    public function setRouteTextColor(?string $routeTextColor): LegDto
    {
        $this->routeTextColor = $routeTextColor;

        return $this;
    }

    public function setMode(string $mode): LegDto
    {
        $this->mode = $mode;

        return $this;
    }

    public function setFrom(StopPlaceDto $from): LegDto
    {
        $this->from = $from;

        return $this;
    }

    public function setTo(StopPlaceDto $to): LegDto
    {
        $this->to = $to;

        return $this;
    }

    public function setDuration(int $duration): LegDto
    {
        $this->duration = $duration;

        return $this;
    }

    public function setStartTime(?Carbon $startTime): LegDto
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function setEndTime(?Carbon $endTime): LegDto
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function setRealTime(bool $realTime): LegDto
    {
        $this->realTime = $realTime;

        return $this;
    }

    public function setHeadSign(string $headSign): LegDto
    {
        $this->headSign = $headSign;

        return $this;
    }

    public function setAgencyName(?string $agencyName): LegDto
    {
        $this->agencyName = $agencyName;

        return $this;
    }

    public function setAgencyId(?string $agencyId): LegDto
    {
        $this->agencyId = $agencyId;

        return $this;
    }

    public function setTripId(string $tripId): LegDto
    {
        $this->tripId = $tripId;

        return $this;
    }

    public function setRouteShortName(string $routeShortName): LegDto
    {
        $this->routeShortName = $routeShortName;

        return $this;
    }

    public function setSource(string $source): LegDto
    {
        $this->source = $source;

        return $this;
    }

    public function setIntermediateStops(array $intermediateStops): LegDto
    {
        $this->intermediateStops = $intermediateStops;

        return $this;
    }

    public function setScheduledStartTime(?Carbon $scheduledStartTime): LegDto
    {
        $this->scheduledStartTime = $scheduledStartTime;

        return $this;
    }

    public function setScheduledEndTime(?Carbon $scheduledEndTime): LegDto
    {
        $this->scheduledEndTime = $scheduledEndTime;

        return $this;
    }

    public function setRouteLongName(?string $routeLongName): LegDto
    {
        $this->routeLongName = $routeLongName;

        return $this;
    }

    public function setTripShortName(?string $tripShortName): LegDto
    {
        $this->tripShortName = $tripShortName;

        return $this;
    }

    public function setDisplayName(?string $displayName): LegDto
    {
        $this->displayName = $displayName;

        return $this;
    }
}
