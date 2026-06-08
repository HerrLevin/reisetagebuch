<?php

namespace Database\Factories;

use App\Models\ActivityPubActor;
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
        $actorId = 'https://remote.example/users/'.$this->faker->unique()->userName();

        return [
            'follower_actor_id' => $actorId,
            'followed_user_id' => User::factory(),
            'activity_pub_actor_id' => ActivityPubActor::factory()->state([
                'actor_uri' => $actorId,
                'inbox_url' => 'https://remote.example/inbox',
                'shared_inbox_url' => 'https://remote.example/shared-inbox',
            ]),
        ];
    }
}
