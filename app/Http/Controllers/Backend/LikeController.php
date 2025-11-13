<?php

namespace App\Http\Controllers\Backend;

use App\Dto\LikeDto;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
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

        return new LikeDto($like->exists, $post->likes()->count());
    }

    public function destroy(User $user, Post $post): LikeDto
    {
        $destroyed = $this->likeRepository->destroy($user->id, $post->id);

        return new LikeDto(! $destroyed, $post->likes()->count());
    }
}
