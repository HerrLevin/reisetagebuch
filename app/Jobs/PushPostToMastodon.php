<?php

namespace App\Jobs;

use App\Http\Resources\PostTypes\BasePost;
use App\Http\Resources\PostTypes\LocationPost;
use App\Models\ActivityPubFollower;
use App\Models\TransportPost;
use App\Services\ActivityPubService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class PushPostToMastodon implements ShouldQueue
{
    use Queueable;

    private readonly ActivityPubService $activityPub;

    public function __construct(
        private readonly BasePost|LocationPost|TransportPost $post,
        ?ActivityPubService $activityPub = null
    ) {
        $this->activityPub = $activityPub ?? app(ActivityPubService::class);
    }

    public function handle(): void
    {
        $user = $this->post->user;

        // Get all followers
        $followers = ActivityPubFollower::whereFollowedUserId($user->id)->get();

        if ($followers->isEmpty()) {
            Log::info('No followers to send activity to for user: '.$user->username);

            return;
        }

        // Create the Create activity
        $createActivity = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => url('/posts/'.$this->post->id),
            'type' => 'Create',
            'actor' => url('/users/'.$user->username),
            'published' => $this->post->getBody(),
            'to' => ['https://www.w3.org/ns/activitystreams#Public'],
            'object' => [
                'id' => url('/posts/'.$this->post->id.'/object'),
                'type' => 'Note',
                'published' => $this->post->publishedAt,
                'attributedTo' => url('/users/'.$user->username),
                'content' => $this->post->getBody(),
                'to' => ['https://www.w3.org/ns/activitystreams#Public'],
            ],
        ];

        foreach ($followers as $follow) {
            $this->activityPub->deliverActivity($user, $follow->follower_actor_id, $createActivity);
        }
    }
}
