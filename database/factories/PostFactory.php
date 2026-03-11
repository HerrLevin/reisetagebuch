<?php

namespace Database\Factories;

use App\Enums\Visibility;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
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
