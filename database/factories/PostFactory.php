<?php

namespace Database\Factories;

use App\Enums\Visibility;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'body' => $this->faker->paragraph(),
            'published_at' => now(),
            'visibility' => Visibility::PUBLIC->value,
        ];
    }
}
