<?php

namespace App\Jobs;

use App\Enums\Visibility;
use App\Hydrators\PostHydrator;
use App\Hydrators\UserHydrator;
use App\Models\ActivityPubFollower;
use App\Models\Post;
use App\Services\ActivityPubService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class PushPostToMastodon implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [30, 120, 600];

    public function __construct(
        private readonly string $postId
    ) {}

    public function handle(ActivityPubService $activityPub): void
    {
        $postModel = Post::with(['user', 'locationPost', 'transportPost'])->find($this->postId);
        if (! $postModel) {
            Log::warning('PushPostToMastodon: Post not found', ['postId' => $this->postId]);

            return;
        }

        if ($postModel->visibility !== Visibility::PUBLIC) {
            Log::info('PushPostToMastodon: Skipping non-public post', ['postId' => $this->postId]);

            return;
        }

        $postDto = app(PostHydrator::class)->modelToDto($postModel);
        $userDto = app(UserHydrator::class)->modelToDto($postModel->user);

        $followersCollectionUrl = route('ap.followers', ['username' => $postModel->user->username]);

        $followers = ActivityPubFollower::whereFollowedUserId($postModel->user->id)->get();

        if ($followers->isEmpty()) {
            Log::info('No followers to send activity to for user: '.$postModel->user->username);

            return;
        }

        $createActivity = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => route('ap.post', ['id' => $postModel->id]).'/activity',
            'type' => 'Create',
            'actor' => route('ap.actor', ['username' => $postModel->user->username]),
            'published' => $postModel->created_at->toISOString(),
            'to' => ['https://www.w3.org/ns/activitystreams#Public'],
            'cc' => [$followersCollectionUrl],
            'object' => [
                'id' => route('ap.post-object', ['id' => $postModel->id]),
                'type' => 'Note',
                'published' => $postModel->created_at->toISOString(),
                'attributedTo' => route('ap.actor', ['username' => $postModel->user->username]),
                'content' => $postDto->getBody() ?? '',
                'to' => ['https://www.w3.org/ns/activitystreams#Public'],
                'cc' => [$followersCollectionUrl],
            ],
        ];

        $usedInboxes = [];

        foreach ($followers as $follow) {
            $inbox = $follow->follower_shared_inbox_url ?? $follow->follower_inbox_url;
            if ($inbox !== null) {
                if (in_array($inbox, $usedInboxes)) {
                    continue;
                }
                $usedInboxes[] = $inbox;
            }
            $activityPub->deliverActivity($userDto, $follow->follower_actor_id, $inbox, $createActivity);
        }
    }
}
