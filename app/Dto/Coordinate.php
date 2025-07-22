<?php

declare(strict_types=1);

namespace App\Dto;

readonly class Coordinate
{
    public float $latitude;

    public float $longitude;

    public function __construct(float $latitude, float $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public function __toString(): string
    {
        return $this->latitude.','.$this->longitude;
    }
}
