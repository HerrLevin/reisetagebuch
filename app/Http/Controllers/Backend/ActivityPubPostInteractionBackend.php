<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Dto\LikeDto;
use App\Http\Controllers\Controller;
use App\Jobs\ActivityPub\SendApPostLikeActivity;
use App\Jobs\ActivityPub\SendApPostUndoLikeActivity;
use App\Repositories\ActivityPubPostRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Str;

class ActivityPubPostInteractionBackend extends Controller
{
    public function __construct(
        private readonly ActivityPubPostRepository $apPostRepository,
        private readonly UserRepository $userRepository,
    ) {}

    public function like(string $userId, string $postId): LikeDto
    {
        $post = $this->apPostRepository->findById($postId);
        if (! $post) {
            abort(404);
        }

        if ($this->apPostRepository->isLikedByUser($userId, $postId)) {
            return new LikeDto(true, $this->apPostRepository->getLikeCount($postId));
        }

        $userDto = $this->userRepository->getUserById($userId);
        $actorUrl = route('ap.actor', ['username' => $userDto->username]);
        $likeActivityId = $actorUrl.'#likes/'.Str::uuid();

        $this->apPostRepository->createLike($userId, $postId, $likeActivityId);

        SendApPostLikeActivity::dispatch($userId, $postId, $likeActivityId);

        return new LikeDto(true, $this->apPostRepository->getLikeCount($postId));
    }

    public function unlike(string $userId, string $postId): LikeDto
    {
        $post = $this->apPostRepository->findById($postId);
        if (! $post) {
            abort(404);
        }

        $likeActivityId = $this->apPostRepository->getLikeActivityId($userId, $postId);

        $this->apPostRepository->deleteLike($userId, $postId);

        if ($likeActivityId) {
            SendApPostUndoLikeActivity::dispatch($userId, $postId, $likeActivityId);
        }

        return new LikeDto(false, $this->apPostRepository->getLikeCount($postId));
    }
}
