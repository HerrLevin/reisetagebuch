<?php

namespace App\Dto\MotisApi;

use Carbon\Carbon;

class TripDto
{
    public int $duration;
    public Carbon $startTime;
    public Carbon $endTime;
    public int $transfers;

    /**
     * @var LegDto[]
     */
    public array $legs;

    public function setDuration(int $duration): TripDto
    {
        $this->duration = $duration;
        return $this;
    }

    public function setStartTime(Carbon $startTime): TripDto
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function setEndTime(Carbon $endTime): TripDto
    {
        $this->endTime = $endTime;
        return $this;
    }

    public function setTransfers(int $transfers): TripDto
    {
        $this->transfers = $transfers;
        return $this;
    }

    public function setLegs(array $legs): TripDto
    {
        $this->legs = $legs;
        return $this;
    }
}
