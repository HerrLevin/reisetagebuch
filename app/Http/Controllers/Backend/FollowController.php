<?php

namespace App\Http\Controllers\Backend;

use App\Exceptions\ConflictException;
use App\Exceptions\InsufficientRightsException;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserDto;
use App\Jobs\CalculateStatisticsForUser;
use App\Models\User;
use App\Notifications\UserFollowedNotification;
use App\Notifications\UserRequestedFollowNotification;
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
     */
    public function getFollowRequests(string $userId, ?User $actingUser = null): array
    {
        if ($actingUser?->id !== $userId) {
            throw new InsufficientRightsException('You don\'t have permission to view this follow relationship');
        }

        return $this->followRepository->getFollowRequests($userId);
    }

    /**
     * @throws ConflictException
     * @throws InsufficientRightsException
     */
    public function createFollowRequest(string $originUserId, string $targetUserId, ?User $actingUser = null): void
    {
        if ($actingUser?->id !== $originUserId) {
            throw new InsufficientRightsException('You don\'t have permission to create this follow relationship');
        }
        if ($originUserId === $targetUserId) {
            throw new ConflictException('Users cannot follow themselves');
        }

        $originUser = $this->userRepository->getUserById($originUserId);
        $targetUser = $this->userRepository->getUserById($targetUserId);

        if (! $targetUser->requiresFollowRequest) {
            throw new ConflictException('You create a follow request. You have to create a follow.');
        }

        $reference = $this->followRepository->createFollowRequest($originUser, $targetUser);
        $this->notificationRepository->notifyUser($targetUser, new UserRequestedFollowNotification($originUser, $reference));
    }

    /**
     * @throws InsufficientRightsException
     */
    public function deleteFollowRequest(string $originUserId, string $targetUserId, ?User $actingUser = null): void
    {
        if ($actingUser?->id !== $originUserId && $actingUser?->id !== $targetUserId) {
            throw new InsufficientRightsException('You don\'t have permission to delete this follow relationship');
        }
        $originUser = $this->userRepository->getUserById($originUserId);
        $targetUser = $this->userRepository->getUserById($targetUserId);

        $reference = $this->followRepository->deleteFollowRequest($originUser, $targetUser);
        $this->notificationRepository->deleteNotificationByReferenceId($reference);
    }

    /**
     * @throws InsufficientRightsException
     */
    public function approveFollowRequest(string $originUserId, string $targetUserId, ?User $actingUser = null): void
    {
        if ($actingUser?->id !== $targetUserId) {
            throw new InsufficientRightsException('You don\'t have permission to approve this follow relationship');
        }
        $originUser = $this->userRepository->getUserById($originUserId);
        $targetUser = $this->userRepository->getUserById($targetUserId);

        $reference = $this->followRepository->deleteFollowRequest($originUser, $targetUser);
        $this->notificationRepository->deleteNotificationByReferenceId($reference);
        $this->storeFollow($originUser, $targetUser);
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

        if ($targetUser->requiresFollowRequest) {
            throw new ConflictException('You cannot follow this relationship. You have to create a follow request!');
        }

        $this->storeFollow($originUser, $targetUser);
    }

    private function storeFollow(UserDto $originUser, UserDto $targetUser): void
    {
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
