<?php

namespace Database\Factories;

use App\Models\UserSettings;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserSettings>
 */
class UserSettingsFactory extends Factory
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
            'motis_radius' => 500,
            'requires_follow_request' => false,
        ];
    }
}
