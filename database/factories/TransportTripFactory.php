<?php

namespace Database\Factories;

use App\Enums\TransportMode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransportTrip>
 */
class TransportTripFactory extends Factory
{
    public function definition(): array
    {
        return [
            'foreign_trip_id' => $this->faker->uuid(),
            'provider' => $this->faker->randomElement(['transitous']),
            'mode' => $this->faker->randomElement(array_column(TransportMode::cases(), 'value')),
            'line_name' => $this->faker->word(),
            'route_long_name' => $this->faker->sentence(3),
            'trip_short_name' => $this->faker->word(),
            'display_name' => $this->faker->sentence(2),
            'route_color' => $this->faker->hexColor(),
            'route_text_color' => $this->faker->hexColor(),
            'last_refreshed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
