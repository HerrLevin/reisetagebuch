<?php

namespace Tests\Unit;

use App\Dto\MotisApi\StopPlaceDto;
use Carbon\Carbon;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function createStopPlaceDto(
        string $stopId = 'fromStopId',
        string $name = 'From Stop',
        float $latitude = 48.8566,
        float $longitude = 2.3522,
        ?Carbon $arrival = null,
        ?Carbon $departure = null
    ): StopPlaceDto {
        $departure = $departure ?? Carbon::now();
        $arrival = $arrival ?? Carbon::now()->addMinutes(5);

        return new StopPlaceDto()
            ->setStopId($stopId)
            ->setName($name)
            ->setLatitude($latitude)
            ->setLongitude($longitude)
            ->setDeparture($departure)
            ->setScheduledDeparture($departure)
            ->setScheduledArrival($arrival)
            ->setArrival($arrival);
    }
}
