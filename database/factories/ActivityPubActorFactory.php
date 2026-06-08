<?php

namespace Database\Factories;

use App\Models\ActivityPubActor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityPubActor>
 */
class ActivityPubActorFactory extends Factory
{
    protected $model = ActivityPubActor::class;

    public function definition(): array
    {
        $username = $this->faker->unique()->userName();

        return [
            'actor_uri' => "https://remote.example/users/{$username}",
            'preferred_username' => $username,
            'display_name' => $this->faker->name(),
            'profile_url' => "https://remote.example/@{$username}",
            'inbox_url' => "https://remote.example/users/{$username}/inbox",
            'shared_inbox_url' => 'https://remote.example/inbox',
        ];
    }
}
