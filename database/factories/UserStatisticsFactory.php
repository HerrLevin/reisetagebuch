<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserStatistics>
 */
class UserStatisticsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'posts_count' => $this->faker->numberBetween(1, 10),
            'transport_posts_count' => $this->faker->numberBetween(1, 10),
            'location_posts_count' => $this->faker->numberBetween(1, 10),
            'followers_count' => $this->faker->numberBetween(1, 10),
            'following_count' => $this->faker->numberBetween(1, 10),
            'travelled_distance' => $this->faker->numberBetween(100, 10000),
            'travelled_duration' => $this->faker->numberBetween(60, 10000),
            'visited_countries_count' => $this->faker->numberBetween(1, 50),
            'visited_locations_count' => $this->faker->numberBetween(1, 200),
        ];
    }
}
