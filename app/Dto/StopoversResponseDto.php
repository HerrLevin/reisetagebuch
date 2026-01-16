<?php

namespace App\Dto;

use App\Dto\MotisApi\TripDto;
use App\Traits\JsonResponseObject;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'StopoversResponseDto',
    description: 'Data Transfer Object for Stopovers Response',
    required: ['trip', 'startTime', 'startId', 'tripId'],
    properties: [
        new OA\Property(
            property: 'trip',
            ref: TripDto::class,
            description: 'Trip Data Transfer Object'
        ),
        new OA\Property(
            property: 'startTime',
            description: 'Start time of the trip',
            type: 'string',
            format: 'date-time'
        ),
        new OA\Property(
            property: 'startId',
            description: 'Identifier for the start location',
            type: 'string'
        ),
        new OA\Property(
            property: 'tripId',
            description: 'Unique identifier for the trip',
            type: 'string'
        ),
    ],
    type: 'object'
)]
readonly class StopoversResponseDto
{
    use JsonResponseObject;

    public function __construct(
        public TripDto $trip,
        public string $startTime,
        public string $startId,
        public string $tripId,
    ) {}
}
