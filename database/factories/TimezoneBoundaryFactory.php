<?php

namespace Database\Factories;

use App\Models\TimezoneBoundary;
use Clickbar\Magellan\Data\Geometries\LineString;
use Clickbar\Magellan\Data\Geometries\MultiPolygon;
use Clickbar\Magellan\Data\Geometries\Point;
use Clickbar\Magellan\Data\Geometries\Polygon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimezoneBoundary>
 */
class TimezoneBoundaryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tzid' => $this->faker->unique()->timezone(),
            'geometry' => $this->squareAround(0, 0, 1),
        ];
    }

    /**
     * A simple square polygon of the given half-size (in degrees) around a lat/lon center,
     * deterministic and independent of the vendored timezone-boundary-builder dataset.
     */
    public function squareAround(float $lat, float $lon, float $halfSize = 0.5): MultiPolygon
    {
        $ring = [
            Point::make($lon - $halfSize, $lat - $halfSize, srid: 4326),
            Point::make($lon + $halfSize, $lat - $halfSize, srid: 4326),
            Point::make($lon + $halfSize, $lat + $halfSize, srid: 4326),
            Point::make($lon - $halfSize, $lat + $halfSize, srid: 4326),
            Point::make($lon - $halfSize, $lat - $halfSize, srid: 4326),
        ];

        return MultiPolygon::make([
            Polygon::make([LineString::make($ring, 4326)], 4326),
        ], 4326);
    }
}
