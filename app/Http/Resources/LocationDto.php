<?php

namespace App\Http\Resources;

use App\Models\Location;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LocationDto',
    description: 'Location Data Object',
    required: ['id', 'name', 'latitude', 'longitude', 'distance', 'tags', 'identifiers'],
    type: 'object'
)]
class LocationDto
{
    #[OA\Property('id', description: 'Location ID', type: 'string', format: 'uuid')]
    public string $id;

    #[OA\Property('name', description: 'Name of the location', type: 'string')]
    public string $name;

    #[OA\Property('latitude', description: 'Latitude of the location', type: 'number', format: 'float')]
    public float $latitude;

    #[OA\Property('longitude', description: 'Longitude of the location', type: 'number', format: 'float')]
    public float $longitude;

    #[OA\Property('distance', description: 'Distance to the location in meters', type: 'integer', nullable: true)]
    public ?int $distance;

    #[OA\Property(
        property: 'identifiers',
        description: 'List of location identifiers',
        type: 'array',
        items: new OA\Items(ref: LocationIdentifierDto::class)
    )]
    /**
     * @var LocationIdentifierDto[]
     */
    public array $identifiers = [];

    #[OA\Property(
        property: 'tags',
        description: 'List of location tags',
        type: 'array',
        items: new OA\Items(ref: LocationTagDto::class)
    )]
    /**
     * @var LocationTagDto[]
     */
    public array $tags;

    public function __construct(Location $location)
    {
        $this->id = $location->id;
        $this->name = $location->name;
        $this->latitude = $location->location->getLatitude();
        $this->longitude = $location->location->getLongitude();
        $this->distance = $location->distance ? round($location->distance) : null;
        $this->tags = $location->tags->map(fn ($tag) => new LocationTagDto($tag))->toArray();
        $this->identifiers = $location->identifiers->map(fn ($identifier) => new LocationIdentifierDto($identifier))->toArray();
    }
}
