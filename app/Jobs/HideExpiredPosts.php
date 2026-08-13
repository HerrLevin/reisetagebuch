<?php

namespace App\Jobs;

use App\Repositories\PostRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class HideExpiredPosts implements ShouldQueue
{
    use Queueable;

    public function handle(PostRepository $postRepository): void
    {
        foreach ($postRepository->getPostsToAutoHide() as $post) {
            HideExpiredPostJob::dispatch($post->id);
        }
    }
}
