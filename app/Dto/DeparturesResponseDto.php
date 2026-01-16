<?php

namespace App\Dto;

use App\Enums\TransportMode;
use App\Traits\JsonResponseObject;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'DeparturesResponseDto',
    description: 'Data Transfer Object for Departures Response',
    required: ['departures', 'modes', 'requestTime', 'requestIdentifier', 'requestLatitude', 'requestLongitude'],
    properties: [
        new OA\Property(
            property: 'departures',
            ref: DeparturesDto::class,
            description: 'Departures Data Transfer Object'
        ),
        new OA\Property(
            property: 'modes',
            description: 'Filter modes applied to the request',
            type: 'array',
            items: new OA\Items(ref: TransportMode::class)
        ),
        new OA\Property(
            property: 'requestTime',
            description: 'Time when the request was made',
            type: 'string',
            format: 'date-time'
        ),
        new OA\Property(
            property: 'requestIdentifier',
            description: 'Unique identifier for the request',
            type: 'string',
            nullable: true
        ),
        new OA\Property(
            property: 'requestLatitude',
            description: 'Latitude of the request location',
            type: 'number',
            format: 'float'
        ),
        new OA\Property(
            property: 'requestLongitude',
            description: 'Longitude of the request location',
            type: 'number',
            format: 'float'
        ),
    ],
    type: 'object'
)]
readonly class DeparturesResponseDto
{
    use JsonResponseObject;

    public function __construct(
        public DeparturesDto $departures,
        public array $modes,
        public string $requestTime,
        public ?string $requestIdentifier,
        public float $requestLatitude,
        public float $requestLongitude
    ) {}
}
