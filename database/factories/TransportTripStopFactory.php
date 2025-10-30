<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\TransportTrip;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransportTripStop>
 */
class TransportTripStopFactory extends Factory
{
    public function definition(): array
    {
        return [
            'transport_trip_id' => TransportTrip::factory()->create()->id,
            'location_id' => Location::factory()->create()->id,
            'arrival_time' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'departure_time' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'arrival_delay' => $this->faker->numberBetween(0, 3600),
            'departure_delay' => $this->faker->numberBetween(0, 3600),
            'stop_sequence' => $this->faker->numberBetween(1, 100),
            'cancelled' => $this->faker->boolean(10),
            'route_segment_id' => null,
        ];
    }
}
