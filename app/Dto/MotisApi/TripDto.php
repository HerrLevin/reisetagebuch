<?php

namespace App\Dto\MotisApi;

use Carbon\Carbon;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MotisTripDto',
    description: 'Data Transfer Object for a Trip in the Motis API',
    required: ['duration', 'startTime', 'endTime', 'transfers', 'legs'],
    type: 'object'
)]
class TripDto
{
    #[OA\Property(
        property: 'duration',
        description: 'Total duration of the trip in seconds',
        type: 'integer'
    )]
    public int $duration;

    #[OA\Property(
        property: 'startTime',
        description: 'Start time of the trip',
        type: 'string',
        format: 'date-time'
    )]
    public Carbon $startTime;

    #[OA\Property(
        property: 'endTime',
        description: 'End time of the trip',
        type: 'string',
        format: 'date-time'
    )]
    public Carbon $endTime;

    #[OA\Property(
        property: 'transfers',
        description: 'Number of transfers during the trip',
        type: 'integer'
    )]
    public int $transfers;

    #[OA\Property(
        property: 'legs',
        description: 'List of legs that make up the trip',
        type: 'array',
        items: new OA\Items(ref: LegDto::class)
    )]
    /**
     * @var LegDto[]
     */
    public array $legs;

    public function setDuration(int $duration): TripDto
    {
        $this->duration = $duration;

        return $this;
    }

    public function setStartTime(Carbon $startTime): TripDto
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function setEndTime(Carbon $endTime): TripDto
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function setTransfers(int $transfers): TripDto
    {
        $this->transfers = $transfers;

        return $this;
    }

    public function setLegs(array $legs): TripDto
    {
        $this->legs = $legs;

        return $this;
    }
}
