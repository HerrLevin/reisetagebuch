<?php

namespace App\Http\Resources;

use App\Models\Location;

class LocationDto
{
    public string $id;

    public string $name;

    public float $latitude;

    public float $longitude;

    public ?int $distance;

    /**
     * @var LocationIdentifierDto[]
     */
    public array $identifiers = [];

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
