<?php

namespace App\Http\Controllers\Backend;

use App\Dto\LikeDto;
use App\Http\Controllers\Controller;
use App\Jobs\DeletePostLikedNotification;
use App\Models\Post;
use App\Models\User;
use App\Notifications\PostLiked;
use App\Repositories\LikeRepository;

class LikeController extends Controller
{
    private LikeRepository $likeRepository;

    public function __construct(LikeRepository $likeRepository)
    {
        $this->likeRepository = $likeRepository;
    }

    public function store(User $user, Post $post): LikeDto
    {
        $like = $this->likeRepository->store($user->id, $post->id);

        if ($like->wasRecentlyCreated) {
            $post->user->notify(new PostLiked($user, $post, $like));
        }

        return new LikeDto($like->exists, $post->likes()->count());
    }

    public function destroy(User $user, Post $post): LikeDto
    {
        $like = $this->likeRepository->getLike($user->id, $post->id);

        if ($like === null) {
            return new LikeDto(false, $post->likes()->count());
        }

        DeletePostLikedNotification::dispatch($post->user_id, $like->id);
        $destroyed = $this->likeRepository->destroy($like->id);

        return new LikeDto(! $destroyed, $post->likes()->count());
    }
}
