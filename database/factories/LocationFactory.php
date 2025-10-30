<?php

namespace Database\Factories;

use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
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
