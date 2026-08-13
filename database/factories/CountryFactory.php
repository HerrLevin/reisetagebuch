<?php

namespace Database\Factories;

use App\Models\Country;
use Clickbar\Magellan\Data\Geometries\LineString;
use Clickbar\Magellan\Data\Geometries\MultiPolygon;
use Clickbar\Magellan\Data\Geometries\Point;
use Clickbar\Magellan\Data\Geometries\Polygon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Country>
 */
class CountryFactory extends Factory
{
    public function definition(): array
    {
        // a small deterministic square, centered wherever the test needs it
        return [
            'iso_a2' => strtoupper($this->faker->unique()->lexify('??')),
            'name' => $this->faker->country(),
            'geometry' => $this->squareAround(0, 0, 1),
        ];
    }

    /**
     * A simple square polygon of the given half-size (in degrees) around a lat/lon center,
     * deterministic and independent of the vendored Natural Earth dataset.
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
