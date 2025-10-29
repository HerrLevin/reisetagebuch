<?php

namespace Tests;

use App\Dto\MotisApi\StopPlaceDto;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    //

    protected function createStopPlaceDto(
        string $stopId = 'fromStopId',
        string $name = 'From Stop',
        float $latitude = 48.8566,
        float $longitude = 2.3522,
        ?Carbon $arrival = null,
        ?Carbon $departure = null,
        bool $cancelled = false
    ): StopPlaceDto {
        $departure = $departure ?? Carbon::now();
        $arrival = $arrival ?? Carbon::now()->addMinutes(5);

        return new StopPlaceDto()
            ->setStopId($stopId)
            ->setName($name)
            ->setLatitude($latitude)
            ->setLongitude($longitude)
            ->setCancelled($cancelled)
            ->setDeparture($departure)
            ->setScheduledDeparture($departure)
            ->setScheduledArrival($arrival)
            ->setArrival($arrival);
    }
}
