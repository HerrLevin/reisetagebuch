<?php

namespace App\Dto;

use App\Http\Resources\LocationHistoryEntryDto;
use App\Http\Resources\TripHistoryEntryDto;
use Illuminate\Support\Collection;

readonly class LocationHistoryDto
{
    /** @var Collection<int, LocationHistoryEntryDto> */
    public Collection $locations;

    /** @var Collection<int, TripHistoryEntryDto> */
    public Collection $trips;

    public function __construct(Collection $locations, Collection $trips)
    {
        $this->trips = $trips;
        $this->locations = $locations;
    }
}
