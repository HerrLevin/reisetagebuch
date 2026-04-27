<?php

namespace App\Http\Controllers\ActivityPub;

use ActivityPhp\Type;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostTypes\BasePost;
use App\Http\Resources\PostTypes\LocationPost;
use App\Http\Resources\PostTypes\TransportPost;
use App\Http\Resources\UserDto;
use App\Hydrators\PostHydrator;
use App\Models\ActivityPubFollower;
use App\Models\ActivityPubLike;
use App\Models\User;
use App\Notifications\ActivityPubPostLikedNotification;
use App\Notifications\ActivityPubUserFollowedNotification;
use App\Repositories\NotificationRepository;
use App\Repositories\PostRepository;
use App\Repositories\UserRepository;
use App\Services\ActivityPubService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MastodonActivityPubController extends Controller
{
    public function __construct(
        private readonly ActivityPubService $activityPubService,
        private readonly UserRepository $userRepository,
        private readonly PostRepository $postRepository,
        private readonly NotificationRepository $notificationRepository,
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

        $data = [
            'type' => 'Person',
            'id' => route('ap.actor', ['username' => $user->username]),
            'following' => route('ap.following', ['username' => $user->username]),
            'followers' => route('ap.followers', ['username' => $user->username]),
            'inbox' => route('ap.inbox', ['username' => $user->username]),
            'outbox' => route('ap.outbox', ['username' => $user->username]),
            'preferredUsername' => $user->username,
            'name' => $user->name,
            'summary' => $user->bio ?? '',
            'url' => url('/@'.$user->username),
            'manuallyApprovesFollowers' => $user->requiresFollowRequest,
            'discoverable' => true,
            'published' => $user->createdAt,
            'endpoints' => [
                'sharedInbox' => route('ap.shared-inbox'),
            ],
            'publicKey' => [
                'id' => route('ap.actor', ['username' => $user->username]).'#main-key',
                'owner' => route('ap.actor', ['username' => $user->username]),
                'publicKeyPem' => $user->publicKeyPem,
            ],
        ];

        if ($user->avatar) {
            $data['icon'] = [
                'type' => 'Image',
                'mediaType' => 'image/jpeg',
                'url' => url($user->avatar),
            ];
        }

        $data['@context'] = [
            'https://www.w3.org/ns/activitystreams',
            'https://w3id.org/security/v1',
            [
                'manuallyApprovesFollowers' => 'as:manuallyApprovesFollowers',
                'toot' => 'http://joinmastodon.org/ns#',
                'schema' => 'http://schema.org#',
                'PropertyValue' => 'schema:PropertyValue',
                'value' => 'schema:value',
                'discoverable' => 'toot:discoverable',
                'indexable' => 'toot:indexable',
                'attributionDomains' => [
                    '@id' => 'toot:attributionDomains',
                    '@type' => '@id',
                ],
                'focalPoint' => [
                    '@container' => '@list',
                    '@id' => 'toot:focalPoint',
                ],
                'alsoKnownAs' => [
                    '@id' => 'toot:alsoKnownAs',
                    '@type' => '@id',
                ],
            ],
        ];
        Log::info('actor response', $data);

        return response()->json(data: $data, options: JSON_UNESCAPED_SLASHES)->header('Content-Type', 'application/activity+json');
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
            $collection = [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'id' => $outboxUrl,
                'type' => 'OrderedCollection',
                'totalItems' => $user->statistics->posts_count,
                'first' => $outboxUrl.'?page=true',
            ];

            return response()->json(data: $collection, options: JSON_UNESCAPED_SLASHES)
                ->header('Content-Type', 'application/activity+json');
        }

        $followersCollectionUrl = route('ap.followers', ['username' => $user->username]);
        $actorUrl = route('ap.actor', ['username' => $user->username]);

        $items = [];
        /** @var BasePost|TransportPost|LocationPost $post */
        foreach ($posts->items as $post) {
            $items[] = [
                'id' => route('ap.post', ['id' => $post->id]).'/activity',
                'type' => 'Create',
                'actor' => $actorUrl,
                'published' => $post->createdAt,
                'to' => ['https://www.w3.org/ns/activitystreams#Public'],
                'cc' => [$followersCollectionUrl],
                'object' => [
                    'id' => route('ap.post-object', ['id' => $post->id]),
                    'type' => 'Note',
                    'published' => $post->publishedAt,
                    'attributedTo' => $actorUrl,
                    'content' => $post->getBody() ?? '',
                    'to' => ['https://www.w3.org/ns/activitystreams#Public'],
                    'cc' => [$followersCollectionUrl],
                ],
            ];
        }

        $page = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $outboxUrl.'?page=true',
            'type' => 'OrderedCollectionPage',
            'partOf' => $outboxUrl,
            'orderedItems' => $items,
        ];

        if ($posts->nextCursor) {
            $page['next'] = url($outboxUrl.'?cursor='.$posts->nextCursor);
        }
        if ($posts->previousCursor) {
            $page['prev'] = url($outboxUrl.'?cursor='.$posts->previousCursor);
        }

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

        // For other activities, just accept
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

        $actorProfile = $this->activityPubService->getActorProfile($followerActorId);

        $follow = ActivityPubFollower::updateOrCreate(
            [
                'follower_actor_id' => $followerActorId,
                'followed_user_id' => $user->id,
            ],
            [
                'follower_shared_inbox_url' => $actorProfile['sharedInbox'] ?? null,
                'follower_inbox_url' => $actorProfile['inbox'] ?? null,
            ]
        );

        if ($follow->wasRecentlyCreated) {
            $this->notificationRepository->notifyUser(
                $user,
                new ActivityPubUserFollowedNotification(
                    followerActorId: $followerActorId,
                    followerPreferredUsername: $actorProfile['preferredUsername'] ?? $followerActorId,
                    followerDisplayName: $actorProfile['name'] ?? null,
                    followerIconUrl: $actorProfile['iconUrl'] ?? null,
                    followerProfileUrl: $actorProfile['url'] ?? null,
                )
            );
        }

        $inboxUrl = $follow->follower_shared_inbox_url ?? $follow->follower_inbox_url;
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

        $follower = ActivityPubFollower::where([
            'follower_actor_id' => $followerActorId,
            'followed_user_id' => $user->id,
        ])->first();

        if (! $follower) {
            // Already unfollowed — idempotent
            return response()->json('', 202);
        }

        $inboxUrl = $follower->follower_shared_inbox_url ?? $follower->follower_inbox_url;
        $follower->delete();

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
                $actorProfile = $this->activityPubService->getActorProfile($actorId);
                $postHydrator = new PostHydrator;
                $postDto = $postHydrator->modelToDto($post);

                $this->notificationRepository->notifyUser(
                    $user,
                    new ActivityPubPostLikedNotification(
                        actorId: $actorId,
                        preferredUsername: $actorProfile['preferredUsername'] ?? $actorId,
                        displayName: $actorProfile['name'] ?? null,
                        iconUrl: $actorProfile['iconUrl'] ?? null,
                        profileUrl: $actorProfile['url'] ?? null,
                        postId: $postId,
                        postBody: $postDto->body ? substr($postDto->body, 0, 50) : null,
                        postSummary: $postDto->getBody(),
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

    private function postData(Request $request, string $id): JsonResponse
    {
        $post = $this->postRepository->getById($id);

        $note = Type::create('Note', [
            'id' => route('ap.post-object', ['id' => $id]),
            'published' => $post->publishedAt,
            'attributedTo' => route('ap.actor', ['username' => $post->user->username]),
            'content' => $post->getBody() ?? '',
            'to' => ['https://www.w3.org/ns/activitystreams#Public'],
        ]);
        $note->set('@context', 'https://www.w3.org/ns/activitystreams');

        return response()->json(data: $note->toArray(), options: JSON_UNESCAPED_SLASHES)->header('Content-Type', 'application/activity+json');
    }

    public function postObject(Request $request, string $id): RedirectResponse|JsonResponse
    {
        if ($this->checkHeader($request)) {
            return redirect()->away(url('/posts/'.$id));
        }

        return $this->postData($request, $id);
    }

    private function buildAcceptActivity(UserDto $user, string $acceptId, array $originalActivity): array
    {
        // Strip @context from the nested activity — it belongs only at the top level
        $object = $originalActivity;
        unset($object['@context']);

        return [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $acceptId,
            'type' => 'Accept',
            'actor' => route('ap.actor', ['username' => $user->username]),
            'object' => $object,
        ];
    }

    private function sendAccept(UserDto $user, array $followActivity, string $inboxUrl): void
    {
        $acceptId = route('ap.actor', ['username' => $user->username]).'#accepts/'.uniqid();
        $accept = $this->buildAcceptActivity($user, $acceptId, $followActivity);

        $this->activityPubService->deliverActivity($user, $followActivity['actor'], $inboxUrl, $accept);
    }

    private function sendAcceptToInbox(UserDto $user, array $activity, string $inboxUrl): void
    {
        $acceptId = route('ap.actor', ['username' => $user->username]).'#accepts/'.uniqid();
        $accept = $this->buildAcceptActivity($user, $acceptId, $activity);

        $this->activityPubService->deliverActivity($user, $activity['actor'], $inboxUrl, $accept);
    }

    public function followers(string $username): JsonResponse
    {
        $user = User::where('username', $username)->first();
        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $count = ActivityPubFollower::where('followed_user_id', $user->id)->count();

        $collection = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => route('ap.followers', ['username' => $user->username]),
            'type' => 'OrderedCollection',
            'totalItems' => $count,
        ];

        return response()->json(data: $collection, options: JSON_UNESCAPED_SLASHES)
            ->header('Content-Type', 'application/activity+json');
    }

    public function following(string $username): JsonResponse
    {
        $user = User::where('username', $username)->first();
        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $collection = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => route('ap.following', ['username' => $user->username]),
            'type' => 'OrderedCollection',
            'totalItems' => 0,
        ];

        return response()->json(data: $collection, options: JSON_UNESCAPED_SLASHES)
            ->header('Content-Type', 'application/activity+json');
    }
}
