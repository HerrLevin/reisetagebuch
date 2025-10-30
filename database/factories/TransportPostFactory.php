<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\TransportTrip;
use App\Models\TransportTripStop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransportPost>
 */
class TransportPostFactory extends Factory
{
    public function definition(): array
    {
        $tripId = TransportTrip::factory()->create()->id;

        return [
            'post_id' => Post::factory()->create()->id,
            'transport_trip_id' => $tripId,
            'origin_stop_id' => TransportTripStop::factory()->create(['transport_trip_id' => $tripId])->id,
            'destination_stop_id' => TransportTripStop::factory()->create(['transport_trip_id' => $tripId])->id,
            'manual_departure' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'manual_arrival' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
        ];
    }
}
