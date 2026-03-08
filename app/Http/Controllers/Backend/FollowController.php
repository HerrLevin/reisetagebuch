<?php

namespace App\Http\Controllers\Backend;

use App\Exceptions\ConflictException;
use App\Exceptions\InsufficientRightsException;
use App\Http\Controllers\Controller;
use App\Jobs\CalculateStatisticsForUser;
use App\Models\User;
use App\Notifications\UserFollowedNotification;
use App\Repositories\FollowRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FollowController extends Controller
{
    private FollowRepository $followRepository;

    private UserRepository $userRepository;

    private NotificationRepository $notificationRepository;

    public function __construct(FollowRepository $followRepository, UserRepository $userRepository, NotificationRepository $notificationRepository)
    {
        $this->followRepository = $followRepository;
        $this->userRepository = $userRepository;
        $this->notificationRepository = $notificationRepository;
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

        $originUser = $this->userRepository->getUserById($originUserId);
        $targetUser = $this->userRepository->getUserById($targetUserId);

        $reference = $this->followRepository->createFollow($originUser, $targetUser);
        $this->notificationRepository->notifyUser($targetUser, new UserFollowedNotification($originUser, $reference));

        CalculateStatisticsForUser::dispatch($originUser->id);
        CalculateStatisticsForUser::dispatch($targetUser->id);
    }

    /**
     * @throws InsufficientRightsException
     */
    public function deleteFollow(string $originUserId, string $targetUserId, ?User $actingUser = null): void
    {
        if ($actingUser?->id !== $originUserId) {
            throw new InsufficientRightsException('You don\'t have permission to delete this follow relationship');
        }

        $reference = $this->followRepository->deleteFollow($originUserId, $targetUserId);
        $this->notificationRepository->deleteNotificationByReferenceId($reference);

        CalculateStatisticsForUser::dispatch($actingUser->id);
        CalculateStatisticsForUser::dispatch($targetUserId);
    }
}
