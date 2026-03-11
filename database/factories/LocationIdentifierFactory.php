<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\LocationIdentifier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LocationIdentifier>
 */
class LocationIdentifierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'location_id' => Location::factory()->create()->id,
            'identifier' => $this->faker->bothify('???-#####'),
            'type' => $this->faker->randomElement(['IATA', 'ICAO', 'Custom']),
            'origin' => $this->faker->randomElement(['osm', 'transitous']),
            'name' => $this->faker->city(),
        ];
    }
}
