<?php

namespace App\Jobs\ActivityPub;

use App\Repositories\PostRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PushLocationTimezoneChangeToMastodon implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $locationId
    ) {}

    public function handle(PostRepository $postRepository): void
    {
        foreach ($postRepository->getActivePostIdsForLocation($this->locationId) as $postId) {
            PushUpdateToMastodon::dispatch($postId);
        }
    }
}
