<?php

namespace App\Http\Controllers\Backend;

use App\Exceptions\ConflictException;
use App\Exceptions\InsufficientRightsException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\FollowRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FollowController extends Controller
{
    private FollowRepository $followRepository;

    public function __construct(FollowRepository $followRepository)
    {
        $this->followRepository = $followRepository;
    }

    public function getFollowings(string $userId): array
    {
        return $this->followRepository->getFollowings($userId);
    }

    public function getFollowers(string $userId): array
    {
        return $this->followRepository->getFollowers($userId);
    }

    /**
     * @throws InsufficientRightsException
     * @throws ConflictException
     * @throws ModelNotFoundException
     */
    public function createFollow(string $originUserId, string $targetUserId, ?User $actingUser = null): void
    {
        if ($actingUser?->id !== $originUserId) {
            throw new InsufficientRightsException('You don\'t have permission to create this follow relationship');
        }
        if ($originUserId === $targetUserId) {
            throw new ConflictException('Users cannot follow themselves');
        }

        $this->followRepository->createFollow($originUserId, $targetUserId);
    }

    /**
     * @throws InsufficientRightsException
     */
    public function deleteFollow(string $originUserId, string $targetUserId, ?User $actingUser = null): void
    {
        if ($actingUser?->id !== $originUserId) {
            throw new InsufficientRightsException('You don\'t have permission to delete this follow relationship');
        }

        $this->followRepository->deleteFollow($originUserId, $targetUserId);
    }
}
