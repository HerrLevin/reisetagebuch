<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\LocationDto;
use App\Models\Location;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LocationDtoTest extends TestCase
{
    #[DataProvider('distanceDataProvider')]
    public function test_distance_is_rounded_to_whole_meters(?float $distance, ?int $expected): void
    {
        $location = new Location;
        $location->forceFill(['id' => 'loc-1', 'name' => 'Berlin', 'location' => Point::makeGeodetic(52.52, 13.405), 'distance' => $distance]);
        $location->setRelation('tags', new Collection);
        $location->setRelation('identifiers', new Collection);

        $this->assertSame($expected, new LocationDto($location)->distance);
    }

    public static function distanceDataProvider(): array
    {
        return [
            'not selected' => [null, null],
            'rounds down' => [12.4, 12],
            'rounds up' => [12.5, 13],
        ];
    }
}
