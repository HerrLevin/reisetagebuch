<?php

namespace Database\Factories;

use App\Models\Location;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'location' => Point::makeGeodetic($this->faker->latitude(), $this->faker->longitude()),
            'type' => $this->faker->randomElement(['airport', 'node']),
        ];
    }
}
