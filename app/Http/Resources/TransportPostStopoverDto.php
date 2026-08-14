<?php

namespace App\Http\Resources;

use App\Models\TransportPostStopoverLog;
use App\Models\TransportTripStop;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TransportPostStopoverDto',
    description: 'A single stopover of a transport post journey, including the user-logged actual arrival/departure',
    required: ['id', 'location', 'sequence', 'scheduledArrivalTime', 'scheduledDepartureTime', 'arrivalDelay', 'departureDelay', 'manualArrivalTime', 'manualDepartureTime'],
    type: 'object'
)]
class TransportPostStopoverDto
{
    #[OA\Property(
        property: 'id',
        description: 'Unique identifier for the stop',
        type: 'string',
        format: 'uuid'
    )]
    public string $id;

    #[OA\Property(
        property: 'location',
        ref: LocationDto::class,
        description: 'Location details of the stop'
    )]
    public LocationDto $location;

    #[OA\Property(
        property: 'sequence',
        description: 'Sequence number of the stop within the trip',
        type: 'integer'
    )]
    public int $sequence;

    #[OA\Property(
        property: 'scheduledArrivalTime',
        description: 'Scheduled arrival time in ISO 8601 format',
        type: 'string',
        format: 'date-time',
        nullable: true
    )]
    public ?string $scheduledArrivalTime = null;

    #[OA\Property(
        property: 'scheduledDepartureTime',
        description: 'Scheduled departure time in ISO 8601 format',
        type: 'string',
        format: 'date-time',
        nullable: true
    )]
    public ?string $scheduledDepartureTime = null;

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

    #[OA\Property(
        property: 'manualArrivalTime',
        description: 'User-logged actual arrival time in ISO 8601 format',
        type: 'string',
        format: 'date-time',
        nullable: true
    )]
    public ?string $manualArrivalTime = null;

    #[OA\Property(
        property: 'manualDepartureTime',
        description: 'User-logged actual departure time in ISO 8601 format',
        type: 'string',
        format: 'date-time',
        nullable: true
    )]
    public ?string $manualDepartureTime = null;

    public function __construct(TransportTripStop $stop, ?TransportPostStopoverLog $log = null)
    {
        $this->id = $stop->id;
        $this->location = new LocationDto($stop->location);
        $this->sequence = $stop->stop_sequence;
        $this->scheduledArrivalTime = $stop->arrival_time?->toIso8601String();
        $this->scheduledDepartureTime = $stop->departure_time?->toIso8601String();
        $this->arrivalDelay = $stop->arrival_delay;
        $this->departureDelay = $stop->departure_delay;
        $this->manualArrivalTime = $log?->manual_arrival?->toIso8601String();
        $this->manualDepartureTime = $log?->manual_departure?->toIso8601String();
    }
}
