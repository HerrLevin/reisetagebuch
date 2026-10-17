<?php

namespace App\Dto\MotisApi;

use Carbon\Carbon;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'StopPlaceDto',
    description: 'Data Transfer Object for a Stop Place in the Motis API',
    required: ['arrival', 'departure', 'scheduledArrival', 'scheduledDeparture', 'cancelled'],
    type: 'object'
)]
class StopPlaceDto extends StopDto
{
    #[OA\Property(
        property: 'arrival',
        description: 'Actual arrival time at the stop place, if available',
        type: 'string',
        format: 'date-time',
        nullable: true
    )]
    public ?Carbon $arrival;

    #[OA\Property(
        property: 'departure',
        description: 'Actual departure time from the stop place, if available',
        type: 'string',
        format: 'date-time',
        nullable: true
    )]
    public ?Carbon $departure;

    #[OA\Property(
        property: 'scheduledArrival',
        description: 'Scheduled arrival time at the stop place',
        type: 'string',
        format: 'date-time',
        nullable: true
    )]
    public ?Carbon $scheduledArrival;

    #[OA\Property(
        property: 'scheduledDeparture',
        description: 'Scheduled departure time from the stop place',
        type: 'string',
        format: 'date-time',
        nullable: true
    )]
    public ?Carbon $scheduledDeparture;

    #[OA\Property(
        property: 'cancelled',
        description: 'Indicates if the stop place is cancelled',
        type: 'boolean',
        nullable: true
    )]
    public ?bool $cancelled = null;

    #[OA\Property(
        property: 'scheduledTrack',
        description: 'Scheduled track for the stop place, if available',
        type: 'string',
        nullable: true
    )]
    public ?string $scheduledTrack = null;

    #[OA\Property(
        property: 'track',
        description: 'Actual track for the stop place, if available',
        type: 'string',
        nullable: true
    )]
    public ?string $track = null;

    public function setScheduledTrack(?string $scheduledTrack = null): StopPlaceDto
    {
        $this->scheduledTrack = $scheduledTrack;

        return $this;
    }

    public function setTrack(?string $track = null): StopPlaceDto
    {
        $this->track = $track;

        return $this;
    }

    public function setCancelled(?bool $cancelled = null): StopPlaceDto
    {
        $this->cancelled = $cancelled;

        return $this;
    }

    public function setArrival(?Carbon $arrival): StopPlaceDto
    {
        $this->arrival = $arrival;

        return $this;
    }

    public function setDeparture(?Carbon $departure): StopPlaceDto
    {
        $this->departure = $departure;

        return $this;
    }

    public function setScheduledArrival(?Carbon $scheduledArrival): StopPlaceDto
    {
        $this->scheduledArrival = $scheduledArrival;

        return $this;
    }

    public function setScheduledDeparture(?Carbon $scheduledDeparture): StopPlaceDto
    {
        $this->scheduledDeparture = $scheduledDeparture;

        return $this;
    }
}
