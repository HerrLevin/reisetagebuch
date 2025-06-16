<?php

namespace App\Dto\MotisApi;

class StopDto
{
    public string $stopId;
    public string $name;
    public float $latitude;
    public float $longitude;
    public ?int $distance = null;

    public function setStopId(string $stopId): StopDto
    {
        $this->stopId = $stopId;
        return $this;
    }

    public function setName(string $name): StopDto
    {
        $this->name = $name;
        return $this;
    }

    public function setLatitude(float $latitude): StopDto
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function setLongitude(float $longitude): StopDto
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function setDistance(?int $distance): StopDto
    {
        $this->distance = $distance;
        return $this;
    }
}
