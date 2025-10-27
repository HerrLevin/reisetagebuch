<?php

namespace App\Dto\MotisApi;

class StopTimeDto
{
    public StopPlaceDto $place;

    public string $mode;

    public bool $realTime;

    public string $headSign;

    public ?string $agencyName = null;

    public ?string $agencyId = null;

    public string $tripId;

    public string $routeShortName;

    public ?string $routeLongName = null;

    public ?string $tripShortName = null;

    public ?string $routeColor = null;

    public ?string $routeTextColor = null;

    public ?string $displayName = null;

    public string $source;

    public function setDisplayName(?string $displayName): StopTimeDto
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function setRouteColor(?string $routeColor): StopTimeDto
    {
        $this->routeColor = $routeColor;

        return $this;
    }

    public function setRouteTextColor(?string $routeTextColor): StopTimeDto
    {
        $this->routeTextColor = $routeTextColor;

        return $this;
    }

    public function setPlace(StopPlaceDto $place): StopTimeDto
    {
        $this->place = $place;

        return $this;
    }

    public function setMode(string $mode): StopTimeDto
    {
        $this->mode = $mode;

        return $this;
    }

    public function setRealTime(bool $realTime): StopTimeDto
    {
        $this->realTime = $realTime;

        return $this;
    }

    public function setHeadSign(string $headSign): StopTimeDto
    {
        $this->headSign = $headSign;

        return $this;
    }

    public function setAgencyName(?string $agencyName): StopTimeDto
    {
        $this->agencyName = $agencyName;

        return $this;
    }

    public function setAgencyId(?string $agencyId): StopTimeDto
    {
        $this->agencyId = $agencyId;

        return $this;
    }

    public function setTripId(string $tripId): StopTimeDto
    {
        $this->tripId = $tripId;

        return $this;
    }

    public function setRouteShortName(string $routeShortName): StopTimeDto
    {
        $this->routeShortName = $routeShortName;

        return $this;
    }

    public function setSource(string $source): StopTimeDto
    {
        $this->source = $source;

        return $this;
    }

    public function setRouteLongName(?string $routeLongName): StopTimeDto
    {
        $this->routeLongName = $routeLongName;

        return $this;
    }

    public function setTripShortName(?string $tripShortName): StopTimeDto
    {
        $this->tripShortName = $tripShortName;

        return $this;
    }
}
