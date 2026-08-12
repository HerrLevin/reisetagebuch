<?php

namespace App\Jobs;

use App\Enums\Visibility;
use App\Http\Resources\PostTypes\TransportPost;
use App\Hydrators\PostHydrator;
use App\Jobs\ActivityPub\PushDeleteToMastodon;
use App\Repositories\PostRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class HideExpiredPostJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $postId
    ) {}

    public function handle(PostRepository $postRepository, PostHydrator $postHydrator): void
    {
        $post = $postRepository->internalGetById($this->postId);

        if ($post === null || $post->visibility === Visibility::PRIVATE) {
            return;
        }

        $post->visibility = Visibility::PRIVATE;
        $post->save();

        if ($post->transportPost !== null) {
            $transportPostDto = $postHydrator->modelToDto($post);
            if ($transportPostDto instanceof TransportPost) {
                TraewellingEditPostJob::dispatch($transportPostDto);
            }
        }

        PushDeleteToMastodon::dispatch($post->id, $post->user_id, $post->user->username);
    }
}
