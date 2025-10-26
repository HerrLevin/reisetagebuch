<?php

declare(strict_types=1);

namespace App\Dto\MotisApi;

use Carbon\Carbon;

class LegDto
{
    public string $mode;

    public StopPlaceDto $from;

    public StopPlaceDto $to;

    public int $duration;

    public ?Carbon $startTime;

    public ?Carbon $endTime;

    public ?Carbon $scheduledStartTime;

    public ?Carbon $scheduledEndTime;

    public bool $realTime;

    public string $headSign;

    public ?string $agencyName;

    public ?string $agencyId;

    public string $tripId;

    public string $routeShortName;

    public ?string $routeLongName = null;

    public ?string $tripShortName = null;

    public ?string $displayName = null;

    public ?string $routeColor = null;

    public ?string $routeTextColor = null;

    public function setRouteColor(?string $routeColor): LegDto
    {
        $this->routeColor = $routeColor;

        return $this;
    }

    public function setRouteTextColor(?string $routeTextColor): LegDto
    {
        $this->routeTextColor = $routeTextColor;

        return $this;
    }

    public string $source;

    /**
     * @var StopPlaceDto[]
     */
    public array $intermediateStops;

    public function setMode(string $mode): LegDto
    {
        $this->mode = $mode;

        return $this;
    }

    public function setFrom(StopPlaceDto $from): LegDto
    {
        $this->from = $from;

        return $this;
    }

    public function setTo(StopPlaceDto $to): LegDto
    {
        $this->to = $to;

        return $this;
    }

    public function setDuration(int $duration): LegDto
    {
        $this->duration = $duration;

        return $this;
    }

    public function setStartTime(?Carbon $startTime): LegDto
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function setEndTime(?Carbon $endTime): LegDto
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function setRealTime(bool $realTime): LegDto
    {
        $this->realTime = $realTime;

        return $this;
    }

    public function setHeadSign(string $headSign): LegDto
    {
        $this->headSign = $headSign;

        return $this;
    }

    public function setAgencyName(?string $agencyName): LegDto
    {
        $this->agencyName = $agencyName;

        return $this;
    }

    public function setAgencyId(?string $agencyId): LegDto
    {
        $this->agencyId = $agencyId;

        return $this;
    }

    public function setTripId(string $tripId): LegDto
    {
        $this->tripId = $tripId;

        return $this;
    }

    public function setRouteShortName(string $routeShortName): LegDto
    {
        $this->routeShortName = $routeShortName;

        return $this;
    }

    public function setSource(string $source): LegDto
    {
        $this->source = $source;

        return $this;
    }

    public function setIntermediateStops(array $intermediateStops): LegDto
    {
        $this->intermediateStops = $intermediateStops;

        return $this;
    }

    public function setScheduledStartTime(?Carbon $scheduledStartTime): LegDto
    {
        $this->scheduledStartTime = $scheduledStartTime;

        return $this;
    }

    public function setScheduledEndTime(?Carbon $scheduledEndTime): LegDto
    {
        $this->scheduledEndTime = $scheduledEndTime;

        return $this;
    }

    public function setRouteLongName(?string $routeLongName): LegDto
    {
        $this->routeLongName = $routeLongName;

        return $this;
    }

    public function setTripShortName(?string $tripShortName): LegDto
    {
        $this->tripShortName = $tripShortName;

        return $this;
    }

    public function setDisplayName(?string $displayName): LegDto
    {
        $this->displayName = $displayName;

        return $this;
    }
}
