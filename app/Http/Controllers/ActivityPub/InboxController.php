<?php

namespace App\Http\Controllers\ActivityPub;

use App\Enums\ActivityPubInteractionType;
use App\Http\Controllers\Controller;
use App\Models\ActivityPubFollower;
use App\Models\ActivityPubInteraction;
use App\Models\Post;
use App\Models\User;
use App\Notifications\RemotePostBoostedNotification;
use App\Notifications\RemotePostLikedNotification;
use App\Notifications\RemotePostRepliedNotification;
use App\Notifications\RemoteUserFollowedNotification;
use App\Services\ActivityPubService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InboxController extends Controller
{
    public function __construct(
        private readonly ActivityPubService $activityPubService,
    ) {}

    public function handle(Request $request, string $username): JsonResponse
    {
        $user = User::where('username', $username)->firstOrFail();
        $payload = $request->json()->all();
        $type = $payload['type'] ?? null;

        match ($type) {
            'Follow' => $this->handleFollow($payload, $user),
            'Undo' => $this->handleUndo($payload, $user),
            'Like' => $this->handleLike($payload, $user),
            'Announce' => $this->handleAnnounce($payload, $user),
            'Create' => $this->handleCreate($payload, $user),
            'Delete' => $this->handleDelete($payload),
            default => Log::info("Unhandled ActivityPub activity type: {$type}"),
        };

        return response()->json(null, 202);
    }

    private function handleFollow(array $payload, User $user): void
    {
        $actorUrl = $payload['actor'];
        $actor = $this->activityPubService->fetchRemoteActor($actorUrl);
        if (! $actor) {
            return;
        }

        $follower = ActivityPubFollower::updateOrCreate(
            [
                'follower_actor_id' => $actorUrl,
                'followed_user_id' => $user->id,
            ],
            [
                'follower_inbox' => $actor['inbox'] ?? '',
                'follower_shared_inbox' => $actor['endpoints']['sharedInbox'] ?? null,
                'follower_username' => $actor['preferredUsername'] ?? '',
                'follower_display_name' => $actor['name'] ?? null,
                'follower_avatar' => $actor['icon']['url'] ?? null,
            ]
        );

        // Send Accept
        $accept = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => url("/ap/users/{$user->username}").'#accept-'.$follower->id,
            'type' => 'Accept',
            'actor' => url("/ap/users/{$user->username}"),
            'object' => $payload,
        ];

        $inbox = $actor['inbox'] ?? null;
        if ($inbox) {
            $this->activityPubService->signAndSend($inbox, $accept, $user);
        }

        // Notify user
        $instance = parse_url($actorUrl, PHP_URL_HOST);
        $user->notify(new RemoteUserFollowedNotification(
            actorUsername: $actor['preferredUsername'] ?? '',
            actorDisplayName: $actor['name'] ?? null,
            actorAvatar: $actor['icon']['url'] ?? null,
            actorInstance: $instance,
        ));
    }

    private function handleUndo(array $payload, User $user): void
    {
        $object = $payload['object'] ?? [];
        $objectType = is_array($object) ? ($object['type'] ?? null) : null;

        match ($objectType) {
            'Follow' => $this->handleUndoFollow($object, $user),
            'Like' => $this->handleUndoLike($object),
            'Announce' => $this->handleUndoAnnounce($object),
            default => null,
        };
    }

    private function handleUndoFollow(array $object, User $user): void
    {
        $actorUrl = $object['actor'] ?? '';
        ActivityPubFollower::where('follower_actor_id', $actorUrl)
            ->where('followed_user_id', $user->id)
            ->delete();
    }

    private function handleUndoLike(array $object): void
    {
        $activityId = $object['id'] ?? '';
        ActivityPubInteraction::where('activity_id', $activityId)->delete();
    }

    private function handleUndoAnnounce(array $object): void
    {
        $activityId = $object['id'] ?? '';
        ActivityPubInteraction::where('activity_id', $activityId)->delete();
    }

    private function handleLike(array $payload, User $user): void
    {
        $objectUrl = $payload['object'] ?? '';
        $post = $this->resolvePost($objectUrl);
        if (! $post) {
            return;
        }

        $actorUrl = $payload['actor'];
        $actor = $this->activityPubService->fetchRemoteActor($actorUrl);
        $instance = parse_url($actorUrl, PHP_URL_HOST);

        ActivityPubInteraction::updateOrCreate(
            ['activity_id' => $payload['id']],
            [
                'type' => ActivityPubInteractionType::LIKE,
                'actor_id' => $actorUrl,
                'actor_username' => $actor['preferredUsername'] ?? '',
                'actor_display_name' => $actor['name'] ?? null,
                'actor_avatar' => $actor['icon']['url'] ?? null,
                'actor_instance' => $instance,
                'post_id' => $post->id,
            ]
        );

        $post->load('user');
        $post->user->notify(new RemotePostLikedNotification(
            actorUsername: $actor['preferredUsername'] ?? '',
            actorDisplayName: $actor['name'] ?? null,
            actorAvatar: $actor['icon']['url'] ?? null,
            actorInstance: $instance,
            postId: $post->id,
            postBody: $post->body ? substr($post->body, 0, 50) : null,
        ));
    }

    private function handleAnnounce(array $payload, User $user): void
    {
        $objectUrl = $payload['object'] ?? '';
        $post = $this->resolvePost($objectUrl);
        if (! $post) {
            return;
        }

        $actorUrl = $payload['actor'];
        $actor = $this->activityPubService->fetchRemoteActor($actorUrl);
        $instance = parse_url($actorUrl, PHP_URL_HOST);

        ActivityPubInteraction::updateOrCreate(
            ['activity_id' => $payload['id']],
            [
                'type' => ActivityPubInteractionType::BOOST,
                'actor_id' => $actorUrl,
                'actor_username' => $actor['preferredUsername'] ?? '',
                'actor_display_name' => $actor['name'] ?? null,
                'actor_avatar' => $actor['icon']['url'] ?? null,
                'actor_instance' => $instance,
                'post_id' => $post->id,
            ]
        );

        $post->load('user');
        $post->user->notify(new RemotePostBoostedNotification(
            actorUsername: $actor['preferredUsername'] ?? '',
            actorDisplayName: $actor['name'] ?? null,
            actorAvatar: $actor['icon']['url'] ?? null,
            actorInstance: $instance,
            postId: $post->id,
            postBody: $post->body ? substr($post->body, 0, 50) : null,
        ));
    }

    private function handleCreate(array $payload, User $user): void
    {
        $object = $payload['object'] ?? [];
        if (! is_array($object) || ($object['type'] ?? '') !== 'Note') {
            return;
        }

        // Check if this is a reply to one of our posts
        $inReplyTo = $object['inReplyTo'] ?? null;
        if (! $inReplyTo) {
            return;
        }

        $post = $this->resolvePost($inReplyTo);
        if (! $post) {
            return;
        }

        $actorUrl = $payload['actor'] ?? $object['attributedTo'] ?? '';
        $actor = $this->activityPubService->fetchRemoteActor($actorUrl);
        $instance = parse_url($actorUrl, PHP_URL_HOST);

        $content = strip_tags($object['content'] ?? '', '<p><br><a><em><strong>');

        ActivityPubInteraction::updateOrCreate(
            ['activity_id' => $object['id'] ?? $payload['id']],
            [
                'type' => ActivityPubInteractionType::REPLY,
                'actor_id' => $actorUrl,
                'actor_username' => $actor['preferredUsername'] ?? '',
                'actor_display_name' => $actor['name'] ?? null,
                'actor_avatar' => $actor['icon']['url'] ?? null,
                'actor_instance' => $instance,
                'post_id' => $post->id,
                'content' => $content,
                'remote_url' => $object['url'] ?? $object['id'] ?? null,
            ]
        );

        $post->load('user');
        $post->user->notify(new RemotePostRepliedNotification(
            actorUsername: $actor['preferredUsername'] ?? '',
            actorDisplayName: $actor['name'] ?? null,
            actorAvatar: $actor['icon']['url'] ?? null,
            actorInstance: $instance,
            postId: $post->id,
            postBody: $post->body ? substr($post->body, 0, 50) : null,
            replyContent: $content ? substr($content, 0, 100) : null,
        ));
    }

    private function handleDelete(array $payload): void
    {
        $object = $payload['object'] ?? '';
        $objectId = is_array($object) ? ($object['id'] ?? '') : $object;

        ActivityPubInteraction::where('remote_url', $objectId)->delete();
        ActivityPubInteraction::where('activity_id', $objectId)->delete();
    }

    private function resolvePost(string $url): ?Post
    {
        // Check if URL matches our post pattern /ap/posts/{id}
        $appUrl = rtrim(config('app.url'), '/');
        $prefix = $appUrl.'/ap/posts/';

        if (str_starts_with($url, $prefix)) {
            $postId = substr($url, strlen($prefix));

            return Post::find($postId);
        }

        return null;
    }
}
