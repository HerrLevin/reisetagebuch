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
        $origin = $this->faker->randomElement(['motis', 'ourairports']);

        return [
            'location_id' => Location::factory()->create()->id,
            'identifier' => $this->faker->bothify('???-#####'),
            'type' => $origin === 'motis' ? 'stop' : $this->faker->randomElement(['IATA', 'ICAO', 'Custom']),
            'origin' => $origin,
            'name' => $this->faker->city(),
        ];
    }
}
