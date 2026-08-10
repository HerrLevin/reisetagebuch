<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Dto\RemoteActorProfileDto;
use App\Dto\RemoteFollowDto;
use App\Dto\RemoteFollowerDto;
use App\Exceptions\InsufficientRightsException;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserDto;
use App\Hydrators\ActivityPub\AcceptHydrator;
use App\Hydrators\ActivityPub\RejectHydrator;
use App\Jobs\ActivityPub\DeliverActivityPubActivity;
use App\Jobs\ActivityPub\SendFollowToRemoteActor;
use App\Jobs\ActivityPub\SendUndoFollowToRemoteActor;
use App\Models\User;
use App\Repositories\ActivityPubFollowerRepository;
use App\Repositories\ActivityPubFollowRequestRepository;
use App\Repositories\ActivityPubRemoteFollowRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\UserRepository;
use App\Repositories\UserStatisticsRepository;
use App\Services\ActivityPubContentSanitizer;
use App\Services\ActivityPubService;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ActivityPubFederationBackend extends Controller
{
    public function __construct(
        private readonly ActivityPubService $activityPubService,
        private readonly ActivityPubRemoteFollowRepository $remoteFollowRepository,
        private readonly ActivityPubFollowerRepository $followerRepository,
        private readonly ActivityPubFollowRequestRepository $followRequestRepository,
        private readonly UserRepository $userRepository,
        private readonly UserStatisticsRepository $userStatisticsRepository,
        private readonly NotificationRepository $notificationRepository,
        private readonly ActivityPubContentSanitizer $contentSanitizer,
    ) {}

    public function resolveHandle(string $userId, string $handle): ?RemoteActorProfileDto
    {
        $profile = $this->activityPubService->resolveActorByHandle($handle);
        if ($profile === null) {
            return null;
        }

        $actorId = $profile['actorId'];
        $followState = $this->remoteFollowRepository->findByUserAndActor($userId, $actorId)?->state;

        return new RemoteActorProfileDto(
            actorId: $actorId,
            displayName: $profile['name'],
            preferredUsername: $profile['preferredUsername'],
            summary: $this->contentSanitizer->sanitize($profile['summary']),
            iconUrl: $profile['iconUrl'],
            profileUrl: $profile['url'],
            followState: $followState,
        );
    }

    /**
     * @return RemoteFollowDto[]
     */
    public function listFollowing(string $userId): array
    {
        return $this->remoteFollowRepository->listForUser($userId)
            ->map(fn ($follow) => new RemoteFollowDto(
                actorId: $follow->remote_actor_id,
                state: $follow->state,
                createdAt: $follow->created_at,
                displayName: $follow->actor?->display_name,
                preferredUsername: $follow->actor?->preferred_username,
                iconUrl: $follow->actor?->local_icon_url,
                profileUrl: $follow->actor?->profile_url,
            ))
            ->values()
            ->all();
    }

    /**
     * @return RemoteFollowerDto[]
     */
    public function listFollowers(string $userId): array
    {
        return $this->followerRepository->listForUser($userId)
            ->map(fn ($follower) => new RemoteFollowerDto(
                actorId: $follower->follower_actor_id,
                createdAt: $follower->created_at,
                displayName: $follower->actor?->display_name,
                preferredUsername: $follower->actor?->preferred_username,
                iconUrl: $follower->actor?->local_icon_url,
                profileUrl: $follower->actor?->profile_url,
            ))
            ->values()
            ->all();
    }

    public function follow(string $userId, string $actorId): void
    {
        if ($this->remoteFollowRepository->findByUserAndActor($userId, $actorId)) {
            return;
        }

        $actor = $this->activityPubService->resolveActor($actorId);
        if ($actor === null) {
            abort(422, 'Could not reach remote actor');
        }

        $userDto = $this->userRepository->getUserById($userId);
        $actorUrl = route('ap.actor', ['username' => $userDto->username]);

        $this->remoteFollowRepository->create(
            userId: $userId,
            remoteActorId: $actorId,
            inboxUrl: $actor->inbox_url,
            sharedInboxUrl: $actor->shared_inbox_url,
            followActivityId: $actorUrl.'#follows/'.Str::uuid(),
        );

        SendFollowToRemoteActor::dispatch($userId, $actorId);
    }

    public function unfollow(string $userId, string $actorId): void
    {
        $record = $this->remoteFollowRepository->delete($userId, $actorId);

        if ($record) {
            SendUndoFollowToRemoteActor::dispatch(
                $userId,
                $actorId,
                $record->follow_activity_id,
                $record->remote_actor_shared_inbox_url ?? $record->remote_actor_inbox_url,
            );
        }
    }

    /**
     * @throws InsufficientRightsException
     */
    public function approveFollowRequest(string $userId, string $requestId, ?User $actingUser = null): void
    {
        if ($actingUser?->id !== $userId) {
            throw new InsufficientRightsException(__('activitypub.follow_request.forbidden_approve'));
        }

        $followRequest = $this->followRequestRepository->findForUser($userId, $requestId);

        $follow = $this->followerRepository->upsertFollow(
            $userId,
            $followRequest->follower_actor_id,
            $followRequest->activity_pub_actor_id,
        );

        if ($follow->wasRecentlyCreated) {
            $this->userStatisticsRepository->incrementFollowersCount($userId);
        }

        $this->notificationRepository->deleteNotificationByReferenceId($followRequest->id);

        $inboxUrl = $followRequest->follower_shared_inbox_url ?? $followRequest->follower_inbox_url;
        $followActivityId = $followRequest->follow_activity_id;
        $followerActorId = $followRequest->follower_actor_id;

        $this->followRequestRepository->delete($followRequest);

        if ($inboxUrl && $followActivityId) {
            $userDto = $this->userRepository->getUserById($userId);

            $followActivity = [
                'id' => $followActivityId,
                'type' => 'Follow',
                'actor' => $followerActorId,
                'object' => route('ap.actor', ['username' => $userDto->username]),
            ];

            try {
                $this->sendAccept($userDto, $followActivity, $inboxUrl);
            } catch (Exception $e) {
                Log::error('Failed to send Accept for approved follow request', [
                    'follower' => $followerActorId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * @throws InsufficientRightsException
     */
    public function rejectFollowRequest(string $userId, string $requestId, ?User $actingUser = null): void
    {
        if ($actingUser?->id !== $userId) {
            throw new InsufficientRightsException(__('activitypub.follow_request.forbidden_reject'));
        }

        $followRequest = $this->followRequestRepository->findForUser($userId, $requestId);

        $this->notificationRepository->deleteNotificationByReferenceId($followRequest->id);

        $inboxUrl = $followRequest->follower_shared_inbox_url ?? $followRequest->follower_inbox_url;
        $followActivityId = $followRequest->follow_activity_id;
        $followerActorId = $followRequest->follower_actor_id;

        $this->followRequestRepository->delete($followRequest);

        if ($inboxUrl && $followActivityId) {
            $userDto = $this->userRepository->getUserById($userId);

            $followActivity = [
                'id' => $followActivityId,
                'type' => 'Follow',
                'actor' => $followerActorId,
                'object' => route('ap.actor', ['username' => $userDto->username]),
            ];

            try {
                $this->sendReject($userDto, $followActivity, $inboxUrl);
            } catch (Exception $e) {
                Log::error('Failed to send Reject for rejected follow request', [
                    'follower' => $followerActorId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function sendAccept(UserDto $user, array $followActivity, string $inboxUrl): void
    {
        $acceptId = route('ap.actor', ['username' => $user->username]).'#accepts/'.Str::uuid();
        $accept = new AcceptHydrator()->hydrate($acceptId, $user, $followActivity)->toArray();

        DeliverActivityPubActivity::dispatch($user, $followActivity['actor'], $inboxUrl, $accept);
    }

    private function sendReject(UserDto $user, array $followActivity, string $inboxUrl): void
    {
        $rejectId = route('ap.actor', ['username' => $user->username]).'#rejects/'.Str::uuid();
        $reject = new RejectHydrator()->hydrate($rejectId, $user, $followActivity)->toArray();

        DeliverActivityPubActivity::dispatch($user, $followActivity['actor'], $inboxUrl, $reject);
    }
}
