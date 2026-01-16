<?php

namespace App\Dto;

use App\Traits\JsonResponseObject;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TripCreationResponseDto',
    description: 'Data Transfer Object for Trip Creation Response',
    required: ['tripId', 'startId', 'startTime'],
    type: 'object'
)]
readonly class TripCreationResponseDto
{
    use JsonResponseObject;

    public function __construct(
        #[OA\Property(description: 'The identifier of the created trip (foreign trip id)', example: 'trip_12345')]
        public string $tripId,

        #[OA\Property(description: 'The identifier of the starting location of the trip', example: 'loc_67890')]
        public string $startId,

        #[OA\Property(description: 'The departure time of the trip in ISO 8601 format', example: '2024-07-01T10:00:00Z')]
        public string $startTime,
    ) {}
}
