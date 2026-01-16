<?php

namespace App\Dto;

use App\Http\Resources\LocationHistoryEntryDto;
use App\Http\Resources\TripHistoryEntryDto;
use App\Traits\JsonResponseObject;
use Illuminate\Support\Collection;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LocationHistoryDto',
    description: 'Data Transfer Object for Location History',
    required: ['locations', 'trips'],
    type: 'object'
)]
readonly class LocationHistoryDto
{
    use JsonResponseObject;

    #[OA\Property(
        property: 'locations',
        description: 'Collection of location history entries',
        type: 'array',
        items: new OA\Items(ref: LocationHistoryEntryDto::class)
    )]
    /** @var Collection<int, LocationHistoryEntryDto> */
    public Collection $locations;

    #[OA\Property(
        property: 'trips',
        description: 'Collection of trip history entries',
        type: 'array',
        items: new OA\Items(ref: TripHistoryEntryDto::class)
    )]
    /** @var Collection<int, TripHistoryEntryDto> */
    public Collection $trips;

    public function __construct(Collection $locations, Collection $trips)
    {
        $this->trips = $trips;
        $this->locations = $locations;
    }
}
