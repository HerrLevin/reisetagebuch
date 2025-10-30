<?php

namespace App\Jobs;

use App\Http\Resources\PostTypes\TransportPost;
use App\Models\Post;
use App\Services\TraewellingRequestService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class TraewellingDeletePostJob implements ShouldQueue
{
    use Queueable;

    private int $traewellingPostId;

    private string $userId;

    public function __construct(TransportPost $transportPost)
    {
        $post = Post::find($transportPost->id);
        $traewellingPostId = $post->metaInfos->where('key', 'traewelling_trip_id')->first()?->value;
        $this->traewellingPostId = (int) $traewellingPostId;
        $this->userId = $post->user_id;
    }

    public function handle(): void
    {
        Log::debug('Deleted post', ['response' => $this->traewellingPostId]);
        if ($this->traewellingPostId === 0) {
            Log::debug('DeletePostInTraewellingJob: traewellingPostId is 0, aborting job.');

            return;
        }

        new TraewellingRequestService()->deletePost($this->traewellingPostId, $this->userId);
    }
}
