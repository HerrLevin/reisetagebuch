<?php

namespace App\Dto;

readonly class OverpassVenue
{
    public string $osmId;
    public ?string $name;
    public float $latitude;
    public float $longitude;
    public string $osmType;
    public array $tags;

    public function __construct(
        string $osmId,
        ?string $name,
        float $latitude,
        float $longitude,
        string $osmType,
        array $tags
    ) {
        $this->osmId = $osmId;
        $this->name = $name;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->osmType = $osmType;
        $this->tags = $tags;
    }
}
