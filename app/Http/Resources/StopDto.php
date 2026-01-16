<?php

namespace App\Http\Resources;

use App\Models\TransportTripStop;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'StopDto',
    description: 'Data Transfer Object for a Transport Trip Stop',
    required: ['id', 'name', 'location', 'arrivalTime', 'departureTime', 'arrivalDelay', 'departureDelay'],
    type: 'object'
)]
class StopDto
{
    #[OA\Property(
        property: 'id',
        description: 'Unique identifier for the stop',
        type: 'string',
        format: 'uuid'
    )]
    public string $id;

    #[OA\Property(
        property: 'name',
        description: 'Name of the stop',
        type: 'string'
    )]
    public string $name;

    #[OA\Property(
        property: 'location',
        ref: LocationDto::class,
        description: 'Location details of the stop'
    )]
    public LocationDto $location;

    #[OA\Property(
        property: 'arrivalTime',
        description: 'Scheduled arrival time in ISO 8601 format',
        type: 'string',
        format: 'date-time',
        nullable: true
    )]
    public ?string $arrivalTime;

    #[OA\Property(
        property: 'departureTime',
        description: 'Scheduled departure time in ISO 8601 format',
        type: 'string',
        format: 'date-time',
        nullable: true
    )]
    public ?string $departureTime;

    #[OA\Property(
        property: 'arrivalDelay',
        description: 'Arrival delay in minutes',
        type: 'integer',
        nullable: true
    )]
    public ?int $arrivalDelay = null;

    #[OA\Property(
        property: 'departureDelay',
        description: 'Departure delay in minutes',
        type: 'integer',
        nullable: true
    )]
    public ?int $departureDelay = null;

    public function __construct(TransportTripStop $stop)
    {
        $this->id = $stop->id;
        $this->name = $stop->location->name;
        $this->location = new LocationDto($stop->location);
        $this->arrivalTime = $stop->arrival_time?->toIso8601String();
        $this->departureTime = $stop->departure_time?->toIso8601String();
        $this->arrivalDelay = $stop->arrival_delay;
        $this->departureDelay = $stop->departure_delay;
    }
}
