<?php

namespace App\Http\Controllers\Backend;

use App\Dto\LikeDto;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserDto;
use App\Jobs\DeletePostLikedNotification;
use App\Models\User;
use App\Notifications\PostLiked;
use App\Repositories\LikeRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\PostRepository;

class LikeController extends Controller
{
    private LikeRepository $likeRepository;

    private PostRepository $postRepository;

    private NotificationRepository $notificationRepository;

    public function __construct(LikeRepository $likeRepository, PostRepository $postRepository, NotificationRepository $notificationRepository)
    {
        $this->likeRepository = $likeRepository;
        $this->postRepository = $postRepository;
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * @return UserDto[]
     */
    public function index(string $postId): array
    {
        $post = $this->postRepository->getById($postId);

        return $this->likeRepository->getLikesByPostId($post->id);
    }

    public function store(User $user, string $postId): LikeDto
    {
        $post = $this->postRepository->getById($postId);
        $like = $this->likeRepository->store($user->id, $postId);

        if ($like->wasRecentlyCreated) {
            $this->notificationRepository->notifyUser($post->user, new PostLiked($user, $post, $like));
        }

        return new LikeDto($like->exists, ++$post->likesCount);
    }

    public function destroy(User $user, string $postId): LikeDto
    {
        $post = $this->postRepository->getById($postId);
        $like = $this->likeRepository->getLike($user->id, $post->id);

        if ($like === null) {
            return new LikeDto(false, $post->likesCount);
        }

        DeletePostLikedNotification::dispatch($post->user->id, $like->id);
        $destroyed = $this->likeRepository->destroy($like->id);

        return new LikeDto(! $destroyed, --$post->likesCount);
    }
}
