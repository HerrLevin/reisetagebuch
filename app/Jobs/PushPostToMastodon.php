<?php

namespace App\Jobs;

use App\Enums\Visibility;
use App\Hydrators\ActivityPub\CreateHydrator;
use App\Hydrators\ActivityPub\NoteHydrator;
use App\Models\ActivityPubFollower;
use App\Repositories\PostRepository;
use App\Services\ActivityPubService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
        try {
            $postDto = app(PostRepository::class)->getById($this->postId, null, false);
        } catch (HttpException|ModelNotFoundException $e) {
            Log::error('PushPostToMastodon: Failed to fetch post', ['postId' => $this->postId, 'error' => $e->getMessage()]);

            return;
        }

        if ($postDto->visibility !== Visibility::PUBLIC) {
            Log::info('PushPostToMastodon: Skipping non-public post', ['postId' => $this->postId]);

            return;
        }

        $followersCollectionUrl = route('ap.followers', ['username' => $postDto->user->username]);

        $followers = ActivityPubFollower::whereFollowedUserId($postDto->user->id)->with('actor')->get();

        if ($followers->isEmpty()) {
            Log::info('No followers to send activity to for user: '.$postDto->user->username);

            return;
        }

        $actorUrl = route('ap.actor', ['username' => $postDto->user->username]);
        $note = new NoteHydrator()->hydrate($postDto, $actorUrl, $followersCollectionUrl);
        $createActivity = new CreateHydrator()->hydrate($actorUrl, $note, true)->toArray();

        $usedInboxes = [];

        foreach ($followers as $follow) {
            $inbox = $follow->follower_shared_inbox_url ?? $follow->follower_inbox_url;
            if ($inbox !== null) {
                if (in_array($inbox, $usedInboxes)) {
                    continue;
                }
                $usedInboxes[] = $inbox;
            }
            $activityPub->deliverActivity($postDto->user, $follow->follower_actor_id, $inbox, $createActivity);
        }
    }
}
