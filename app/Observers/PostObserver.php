<?php

namespace App\Observers;

use App\Enums\Visibility;
use App\Jobs\DeliverActivityToFollowers;
use App\Models\Post;
use App\Services\ActivityPubService;

class PostObserver
{
    public function __construct(
        private readonly ActivityPubService $activityPubService,
    ) {}

    public function created(Post $post): void
    {
        if (! $this->shouldFederate($post)) {
            return;
        }

        $activity = $this->activityPubService->buildCreateActivity($post);
        DeliverActivityToFollowers::dispatch($post->user_id, $activity);
    }

    public function updated(Post $post): void
    {
        if (! $this->shouldFederate($post)) {
            return;
        }

        $activity = $this->activityPubService->buildUpdateActivity($post);
        DeliverActivityToFollowers::dispatch($post->user_id, $activity);
    }

    public function deleting(Post $post): void
    {
        if (! $this->shouldFederate($post)) {
            return;
        }

        $activity = $this->activityPubService->buildDeleteActivity($post);
        DeliverActivityToFollowers::dispatch($post->user_id, $activity);
    }

    private function shouldFederate(Post $post): bool
    {
        return in_array($post->visibility, [Visibility::PUBLIC, Visibility::UNLISTED]);
    }
}
