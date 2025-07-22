<?php

namespace App\Dto;

readonly class OverpassLocation
{
    public string $osmId;

    public float $latitude;

    public float $longitude;

    public string $osmType;

    public array $tags;

    public function __construct(
        string $osmId,
        float $latitude,
        float $longitude,
        string $osmType,
        array $tags
    ) {
        $this->osmId = $osmId;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->osmType = $osmType;
        $this->tags = $tags;
    }
}
