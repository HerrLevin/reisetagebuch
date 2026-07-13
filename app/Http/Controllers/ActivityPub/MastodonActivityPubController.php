<?php

namespace App\Http\Controllers\ActivityPub;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostTypes\BasePost;
use App\Http\Resources\PostTypes\LocationPost;
use App\Http\Resources\PostTypes\TransportPost;
use App\Http\Resources\UserDto;
use App\Hydrators\ActivityPub\AcceptHydrator;
use App\Hydrators\ActivityPub\CreateHydrator;
use App\Hydrators\ActivityPub\NoteHydrator;
use App\Hydrators\ActivityPub\OrderedCollectionHydrator;
use App\Hydrators\ActivityPub\OrderedCollectionPageHydrator;
use App\Hydrators\ActivityPub\PersonHydrator;
use App\Hydrators\PostHydrator;
use App\Models\ActivityPubActor;
use App\Models\ActivityPubFollower;
use App\Models\ActivityPubInboxItem;
use App\Models\ActivityPubLike;
use App\Models\User;
use App\Notifications\ActivityPubPostLikedNotification;
use App\Notifications\ActivityPubUserFollowedNotification;
use App\Repositories\ActivityPubPostRepository;
use App\Repositories\ActivityPubRemoteFollowRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\PostRepository;
use App\Repositories\UserRepository;
use App\Repositories\UserStatisticsRepository;
use App\Services\ActivityPubService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MastodonActivityPubController extends Controller
{
    public function __construct(
        private readonly ActivityPubService $activityPubService,
        private readonly UserRepository $userRepository,
        private readonly PostRepository $postRepository,
        private readonly NotificationRepository $notificationRepository,
        private readonly ActivityPubRemoteFollowRepository $remoteFollowRepository,
        private readonly UserStatisticsRepository $userStatisticsRepository,
        private readonly ActivityPubPostRepository $activityPubPostRepository,
    ) {}

    private function checkHeader(Request $request): bool
    {
        $accept = $request->header('accept', '');

        return ! (str_contains($accept, 'application/ld+json') || str_contains($accept, 'application/activity+json') || str_contains($accept, 'application/json'));
    }

    public function actor(Request $request, string $username): JsonResponse|RedirectResponse
    {
        if ($this->checkHeader($request)) {
            return redirect()->away(url('/profile/'.$username));
        }
        $user = $this->userRepository->getUserByUsername($username);

        $hydrator = new PersonHydrator;
        $person = $hydrator->hydrate($user)->toArray();
        Log::info('actor response', $person);

        return response()->json(data: $person, options: JSON_UNESCAPED_SLASHES)->header('Content-Type', 'application/activity+json');
    }

    public function outbox(Request $request, string $username): JsonResponse
    {
        $user = User::where('username', $username)->first();
        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $outboxUrl = route('ap.outbox', ['username' => $user->username]);
        $posts = $this->postRepository->getPostsForUserId($user->id);

        if (! $request->has('page') && ! $request->has('cursor')) {
            $collection = new OrderedCollectionHydrator()->hydrate(
                id: $outboxUrl,
                totalItems: $user->statistics->posts_count,
                first: $outboxUrl.'?page=true',
            )->toArray();

            return response()->json(data: $collection, options: JSON_UNESCAPED_SLASHES)
                ->header('Content-Type', 'application/activity+json');
        }

        $followersCollectionUrl = route('ap.followers', ['username' => $user->username]);
        $actorUrl = route('ap.actor', ['username' => $user->username]);

        $items = [];
        /** @var BasePost|TransportPost|LocationPost $post */
        foreach ($posts->items as $post) {
            $note = new NoteHydrator()->hydrate($post, $actorUrl, $followersCollectionUrl);
            $items[] = new CreateHydrator()->hydrate(
                $actorUrl,
                $note
            );
        }

        $page = new OrderedCollectionPageHydrator()->hydrate(
            id: $outboxUrl.'?page=true',
            partOf: $outboxUrl,
            items: $items,
            next: $posts->nextCursor ? url($outboxUrl.'?cursor='.$posts->nextCursor) : null,
            prev: $posts->previousCursor ? url($outboxUrl.'?cursor='.$posts->previousCursor) : null,
        )->toArray();

        return response()->json(data: $page, options: JSON_UNESCAPED_SLASHES)
            ->header('Content-Type', 'application/activity+json');
    }

    public function inbox(Request $request, string $username): JsonResponse
    {
        if (! $this->isActivityPubContentType($request)) {
            return response()->json(['error' => 'Unsupported Media Type'], 415);
        }

        $user = $this->userRepository->getUserByUsername($username);
        $activity = $request->json()->all();

        return $this->processActivity($activity, $user);
    }

    public function sharedInbox(Request $request): JsonResponse
    {
        if (! $this->isActivityPubContentType($request)) {
            return response()->json(['error' => 'Unsupported Media Type'], 415);
        }

        $activity = $request->json()->all();
        $type = $activity['type'] ?? null;

        // Global activities: Create(Note) and Delete(Note) have no specific local user target
        if ($type === 'Create') {
            return $this->processGlobalActivity($activity);
        }

        if ($type === 'Delete' && ! $this->isActorDelete($activity)) {
            return $this->processGlobalActivity($activity);
        }

        // Determine the target user from the activity
        $targetActorId = null;
        if (isset($activity['object']) && is_string($activity['object'])) {
            $targetActorId = $activity['object'];
        } elseif (isset($activity['object']['object']) && is_string($activity['object']['object'])) {
            $targetActorId = $activity['object']['object'];
        }

        if (! $targetActorId) {
            return response()->json('', 202);
        }

        // Extract username from actor URL
        $actorRoute = route('ap.actor', ['username' => 'PLACEHOLDER']);
        $prefix = str_replace('PLACEHOLDER', '', $actorRoute);
        if (str_starts_with($targetActorId, $prefix)) {
            $username = substr($targetActorId, strlen($prefix));
            try {
                $user = $this->userRepository->getUserByUsername($username);

                return $this->processActivity($activity, $user);
            } catch (\Exception) {
                Log::warning('Shared inbox: user not found', ['targetActorId' => $targetActorId]);
            }
        }

        return response()->json('', 202);
    }

    private function isActorDelete(array $activity): bool
    {
        $actorUri = $activity['actor'] ?? null;
        $object = $activity['object'] ?? null;
        $objectId = is_string($object) ? $object : ($object['id'] ?? null);

        return $objectId !== null && $objectId === $actorUri;
    }

    private function processGlobalActivity(array $activity): JsonResponse
    {
        $type = $activity['type'] ?? null;
        $activityId = $activity['id'] ?? null;
        $actorId = $activity['actor'] ?? null;

        if ($activityId && $actorId) {
            $inserted = ActivityPubInboxItem::insertOrIgnore([
                'activity_id' => $activityId,
                'actor_id' => $actorId,
                'activity_type' => $type,
            ]);

            if (! $inserted) {
                return response()->json('', 202);
            }
        }

        if ($type === 'Create') {
            return $this->handleCreate($activity);
        }

        if ($type === 'Delete') {
            return $this->handleNoteDelete($activity);
        }

        return response()->json('', 202);
    }

    private function handleCreate(array $activity): JsonResponse
    {
        $actorId = $activity['actor'] ?? null;
        $object = $activity['object'] ?? null;

        if (! $actorId || ! is_array($object) || ($object['type'] ?? null) !== 'Note') {
            return response()->json('', 202);
        }

        $noteId = $object['id'] ?? null;
        $content = $object['content'] ?? null;
        $objectUrl = $object['url'] ?? null;
        $published = $object['published'] ?? null;

        if (! $noteId) {
            return response()->json('', 202);
        }

        // Only store if we know the actor (i.e. someone follows them)
        $actor = ActivityPubActor::where('actor_uri', $actorId)->first();
        if (! $actor) {
            Log::info('Create(Note): unknown actor, ignoring', ['actorId' => $actorId]);

            return response()->json('', 202);
        }

        $this->activityPubPostRepository->findOrCreateByActivityId(
            activityPubActorId: $actor->id,
            activityId: $noteId,
            url: is_string($objectUrl) ? $objectUrl : null,
            content: $content,
            publishedAt: $published ? Carbon::parse($published) : Carbon::now(),
        );

        Log::info('Stored AP post', ['noteId' => $noteId, 'actor' => $actorId]);

        return response()->json('', 202);
    }

    private function handleNoteDelete(array $activity): JsonResponse
    {
        $object = $activity['object'] ?? null;
        $noteId = is_string($object) ? $object : ($object['id'] ?? null);

        if (! $noteId) {
            return response()->json('', 202);
        }

        $this->activityPubPostRepository->deleteByActivityId($noteId);

        return response()->json('', 202);
    }

    private function isActivityPubContentType(Request $request): bool
    {
        $contentType = $request->header('content-type', '');

        return str_contains($contentType, 'application/activity+json')
            || str_contains($contentType, 'application/ld+json')
            || str_contains($contentType, 'application/json');
    }

    private function processActivity(array $activity, UserDto $user): JsonResponse
    {
        $type = $activity['type'] ?? null;
        $activityId = $activity['id'] ?? null;
        $actorId = $activity['actor'] ?? null;

        if ($activityId && $actorId) {
            $inserted = ActivityPubInboxItem::insertOrIgnore([
                'activity_id' => $activityId,
                'actor_id' => $actorId,
                'activity_type' => $type,
            ]);

            if (! $inserted) {
                Log::info('ActivityPub: duplicate activity ignored', ['activityId' => $activityId, 'actorId' => $actorId]);

                return response()->json('', 202);
            }
        }

        Log::info('processing activity', ['type' => $type]);

        if ($type === 'Follow') {
            return $this->handleFollow($activity, $user);
        }
        if ($type === 'Like') {
            return $this->handleLike($activity, $user);
        }
        if ($type === 'Undo' && isset($activity['object']['type']) && $activity['object']['type'] === 'Follow') {
            return $this->handleUndoFollow($activity, $user);
        }
        if ($type === 'Undo' && isset($activity['object']['type']) && $activity['object']['type'] === 'Like') {
            return $this->handleUndoLike($activity, $user);
        }
        if ($type === 'Update') {
            return $this->handleUpdate($activity);
        }
        if ($type === 'Delete') {
            return $this->handleDelete($activity);
        }
        if ($type === 'Accept') {
            return $this->handleAccept($activity, $user);
        }
        if ($type === 'Reject') {
            return $this->handleReject($activity, $user);
        }

        // For other activities, just accept
        return response()->json('', 202);
    }

    private function handleAccept(array $activity, UserDto $user): JsonResponse
    {
        $object = $activity['object'] ?? null;
        $followActivityId = is_string($object) ? $object : ($object['id'] ?? null);

        if ($followActivityId) {
            $this->remoteFollowRepository->updateStateByFollowActivityId($user->id, $followActivityId, 'accepted');
        }

        return response()->json('', 202);
    }

    private function handleReject(array $activity, UserDto $user): JsonResponse
    {
        $object = $activity['object'] ?? null;
        $followActivityId = is_string($object) ? $object : ($object['id'] ?? null);

        if ($followActivityId) {
            $this->remoteFollowRepository->updateStateByFollowActivityId($user->id, $followActivityId, 'rejected');
        }

        return response()->json('', 202);
    }

    private function handleFollow($activity, UserDto $user): JsonResponse
    {
        $followerActorId = $activity['actor'];
        $followedActorId = $activity['object'];

        if ($followedActorId !== route('ap.actor', ['username' => $user->username])) {
            Log::info('invalid follow object', [$user->username, $followedActorId]);

            return response()->json(['error' => 'Invalid follow object'], 400);
        }

        $actor = $this->activityPubService->resolveActor($followerActorId);

        $follow = ActivityPubFollower::updateOrCreate(
            [
                'follower_actor_id' => $followerActorId,
                'followed_user_id' => $user->id,
            ],
            [
                'activity_pub_actor_id' => $actor?->id,
            ]
        );

        if ($follow->wasRecentlyCreated) {
            $this->userStatisticsRepository->incrementFollowersCount($user->id);
            $this->notificationRepository->notifyUser(
                $user,
                new ActivityPubUserFollowedNotification(
                    followerActorId: $followerActorId,
                    followerPreferredUsername: $actor?->preferred_username ?? $followerActorId,
                    followerDisplayName: $actor?->display_name,
                    followerIconUrl: $actor?->local_icon_url,
                    followerProfileUrl: $actor?->profile_url,
                )
            );
        }

        $inboxUrl = $actor?->shared_inbox_url ?? $actor?->inbox_url;
        if ($inboxUrl) {
            try {
                $this->sendAccept($user, $activity, $inboxUrl);
            } catch (\Exception $e) {
                Log::error('Failed to send Accept for follow', [
                    'follower' => $followerActorId,
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            Log::warning('No inbox URL available to send Accept', ['follower' => $followerActorId]);
        }

        return response()->json('', 202);
    }

    private function handleUndoFollow($activity, UserDto $user): JsonResponse
    {
        $object = $activity['object'];
        $followerActorId = $object['actor'];
        $followedActorId = $object['object'];

        if ($followedActorId !== route('ap.actor', ['username' => $user->username])) {
            Log::info('invalid follow object', [$user->username, $followedActorId]);

            return response()->json(['error' => 'Invalid follow object'], 400);
        }

        $follower = ActivityPubFollower::with('actor')->where([
            'follower_actor_id' => $followerActorId,
            'followed_user_id' => $user->id,
        ])->first();

        if (! $follower) {
            // Already unfollowed — idempotent
            return response()->json('', 202);
        }

        $inboxUrl = $follower->follower_shared_inbox_url ?? $follower->follower_inbox_url;
        $follower->delete();
        $this->userStatisticsRepository->decrementFollowersCount($user->id);

        if ($inboxUrl) {
            try {
                $this->sendAcceptToInbox($user, $activity, $inboxUrl);
            } catch (\Exception $e) {
                Log::error('Failed to send Accept for undo follow', [
                    'follower' => $followerActorId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json('', 202);
    }

    private function parsePostIdFromUrl(string $url): ?string
    {
        // Match URLs like https://example.com/ap/posts/{id} or /ap/posts/{id}/object
        $postRoute = route('ap.post', ['id' => 'PLACEHOLDER']);
        $prefix = str_replace('PLACEHOLDER', '', $postRoute);

        if (str_starts_with($url, $prefix)) {
            $remainder = substr($url, strlen($prefix));
            // Remove trailing /object if present
            $postId = rtrim($remainder, '/');
            $postId = preg_replace('#/object$#', '', $postId);

            return $postId ?: null;
        }

        return null;
    }

    private function handleLike(array $activity, UserDto $user): JsonResponse
    {
        $actorId = $activity['actor'] ?? null;
        $objectUrl = $activity['object'] ?? null;

        if (! $actorId || ! is_string($objectUrl)) {
            return response()->json('', 202);
        }

        $postId = $this->parsePostIdFromUrl($objectUrl);
        if (! $postId) {
            Log::info('Like activity: could not parse post ID from object URL', ['object' => $objectUrl]);

            return response()->json('', 202);
        }

        $post = $this->postRepository->internalGetById($postId);
        if (! $post || $post->user_id !== $user->id) {
            Log::info('Like activity: post not found or does not belong to user', ['postId' => $postId, 'userId' => $user->id]);

            return response()->json('', 202);
        }

        $like = ActivityPubLike::updateOrCreate(
            [
                'actor_id' => $actorId,
                'post_id' => $postId,
            ],
            [
                'activity_id' => $activity['id'] ?? null,
            ]
        );

        if ($like->wasRecentlyCreated) {
            try {
                $actor = $this->activityPubService->resolveActor($actorId);
                $postHydrator = new PostHydrator;
                $postDto = $postHydrator->modelToDto($post);

                $this->notificationRepository->notifyUser(
                    $user,
                    new ActivityPubPostLikedNotification(
                        actorId: $actorId,
                        preferredUsername: $actor?->preferred_username ?? $actorId,
                        displayName: $actor?->display_name,
                        iconUrl: $actor?->local_icon_url,
                        profileUrl: $actor?->profile_url,
                        postId: $postId,
                        postBody: $postDto->body ? substr($postDto->body, 0, 50) : null,
                        postSummary: $postDto->getSummary(),
                    )
                );
            } catch (\Exception $e) {
                Log::error('Failed to send notification for AP like', [
                    'actor' => $actorId,
                    'postId' => $postId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json('', 202);
    }

    private function handleUndoLike(array $activity, UserDto $user): JsonResponse
    {
        $object = $activity['object'] ?? [];
        $actorId = $object['actor'] ?? $activity['actor'] ?? null;
        $objectUrl = $object['object'] ?? null;

        if (! $actorId || ! is_string($objectUrl)) {
            return response()->json('', 202);
        }

        $postId = $this->parsePostIdFromUrl($objectUrl);
        if (! $postId) {
            return response()->json('', 202);
        }

        ActivityPubLike::where([
            'actor_id' => $actorId,
            'post_id' => $postId,
        ])->delete();

        return response()->json('', 202);
    }

    private function handleUpdate(array $activity): JsonResponse
    {
        $actorUri = $activity['actor'] ?? null;
        $object = $activity['object'] ?? null;

        if (! $actorUri || ! is_array($object)) {
            return response()->json('', 202);
        }

        $objectType = $object['type'] ?? null;
        $objectId = $object['id'] ?? null;
        $personTypes = ['Person', 'Service', 'Organization', 'Application', 'Group'];

        if (! in_array($objectType, $personTypes) || $objectId !== $actorUri) {
            return response()->json('', 202);
        }

        // Only process updates for actors we already know about
        if (! ActivityPubActor::where('actor_uri', $actorUri)->exists()) {
            Log::info('Received update for unknown actor, ignoring', ['actorUri' => $actorUri]);

            return response()->json('', 202);
        }

        $this->activityPubService->resolveActor($actorUri);
        Log::info('Processed update activity for actor', ['actorUri' => $actorUri]);

        return response()->json('', 202);
    }

    private function handleDelete(array $activity): JsonResponse
    {
        $actorUri = $activity['actor'] ?? null;
        $object = $activity['object'] ?? null;

        // Account deletion: object is the actor URI itself (string) or object.id === actor
        $objectId = is_string($object) ? $object : ($object['id'] ?? null);

        if (! $actorUri || $objectId !== $actorUri) {
            return response()->json('', 202);
        }

        $actor = ActivityPubActor::where('actor_uri', $actorUri)->first();
        if ($actor) {
            if ($actor->local_icon_path) {
                Storage::disk('public')->delete($actor->local_icon_path);
            }
            $actor->delete();
        }

        // Clean up related records
        ActivityPubFollower::where('follower_actor_id', $actorUri)->delete();
        ActivityPubLike::where('actor_id', $actorUri)->delete();

        return response()->json('', 202);
    }

    private function postData(Request $request, string $id): JsonResponse
    {
        $post = $this->postRepository->getById($id);

        $note = new NoteHydrator()->hydrate(
            post: $post,
            actorUrl: route('ap.actor', ['username' => $post->user->username]),
            followersUrl: route('ap.followers', ['username' => $post->user->username]),
            context: true
        );

        return response()->json(data: $note->toArray(), options: JSON_UNESCAPED_SLASHES)->header('Content-Type', 'application/activity+json');
    }

    public function postObject(Request $request, string $id): RedirectResponse|JsonResponse
    {
        if ($this->checkHeader($request)) {
            return redirect()->away(url('/posts/'.$id));
        }

        return $this->postData($request, $id);
    }

    private function sendAccept(UserDto $user, array $followActivity, string $inboxUrl): void
    {
        $acceptId = route('ap.actor', ['username' => $user->username]).'#accepts/'.uniqid();
        $accept = new AcceptHydrator()->hydrate($acceptId, $user, $followActivity)->toArray();

        $this->activityPubService->deliverActivity($user, $followActivity['actor'], $inboxUrl, $accept);
    }

    private function sendAcceptToInbox(UserDto $user, array $activity, string $inboxUrl): void
    {
        $acceptId = route('ap.actor', ['username' => $user->username]).'#accepts/'.uniqid();
        $accept = new AcceptHydrator()->hydrate($acceptId, $user, $activity)->toArray();

        $this->activityPubService->deliverActivity($user, $activity['actor'], $inboxUrl, $accept);
    }

    public function followers(string $username): JsonResponse
    {
        $user = User::with('statistics')->where('username', $username)->first();
        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $collection = new OrderedCollectionHydrator()->hydrate(
            id: route('ap.followers', ['username' => $user->username]),
            totalItems: $user->statistics?->followers_count ?? 0,
        )->toArray();

        return response()->json(data: $collection, options: JSON_UNESCAPED_SLASHES)
            ->header('Content-Type', 'application/activity+json');
    }

    public function following(string $username): JsonResponse
    {
        $user = User::with('statistics')->where('username', $username)->first();
        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $collection = new OrderedCollectionHydrator()->hydrate(
            id: route('ap.following', ['username' => $user->username]),
            totalItems: $user->statistics?->following_count ?? 0,
        )->toArray();

        return response()->json(data: $collection, options: JSON_UNESCAPED_SLASHES)
            ->header('Content-Type', 'application/activity+json');
    }
}
