<?php

namespace App\Jobs;

use App\Repositories\PostRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class HideExpiredPosts implements ShouldQueue
{
    use Queueable;

    private PostRepository $postRepository;

    public function __construct(?PostRepository $postRepository = null)
    {
        $this->postRepository = $postRepository ?? app(PostRepository::class);
    }

    public function handle(): void
    {
        foreach ($this->postRepository->getPostsToAutoHide() as $post) {
            HideExpiredPostJob::dispatch($post->id);
        }
    }
}
