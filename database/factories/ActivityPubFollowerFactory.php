<?php

namespace Database\Factories;

use App\Models\ActivityPubFollower;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityPubFollower>
 */
class ActivityPubFollowerFactory extends Factory
{
    protected $model = ActivityPubFollower::class;

    public function definition(): array
    {
        return [
            'follower_actor_id' => 'https://remote.example/users/'.$this->faker->unique()->userName(),
            'followed_user_id' => User::factory(),
            'follower_inbox_url' => 'https://remote.example/inbox',
            'follower_shared_inbox_url' => 'https://remote.example/shared-inbox',
        ];
    }
}
