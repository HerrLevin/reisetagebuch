<?php

namespace App\Http\Resources;

use App\Models\Location;

class LocationDto
{
    public string $id;
    public string $name;
    public float $latitude;
    public float $longitude;
    public ?float $distance;
    /**
     * @var LocationTagDto[]
     */
    public array $tags;

    public function __construct(Location $location) {
        $this->id = $location->id;
        $this->name = $location->name;
        $this->latitude = $location->latitude;
        $this->longitude = $location->longitude;
        $this->distance = $location->distance ?? null;
        $this->tags = $location->tags->map(fn($tag) => new LocationTagDto($tag))->toArray();
    }
}
