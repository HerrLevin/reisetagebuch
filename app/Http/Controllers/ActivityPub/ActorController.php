<?php

namespace App\Http\Controllers\ActivityPub;

use App\Enums\Visibility;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use App\Services\ActivityPubService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActorController extends Controller
{
    public function __construct(
        private readonly ActivityPubService $activityPubService,
    ) {}

    public function show(string $username): JsonResponse
    {
        $user = User::where('username', $username)->firstOrFail();
        $actorUrl = url("/ap/users/{$user->username}");

        $avatar = $user->profile?->avatar
            ? url("/files/{$user->profile->avatar}")
            : null;

        $actor = [
            '@context' => [
                'https://www.w3.org/ns/activitystreams',
                'https://w3id.org/security/v1',
            ],
            'id' => $actorUrl,
            'type' => 'Person',
            'preferredUsername' => $user->username,
            'name' => $user->name,
            'summary' => $user->profile?->bio ?? '',
            'url' => url("/@{$user->username}"),
            'inbox' => url("/ap/users/{$user->username}/inbox"),
            'outbox' => url("/ap/users/{$user->username}/outbox"),
            'followers' => url("/ap/users/{$user->username}/followers"),
            'publicKey' => [
                'id' => "{$actorUrl}#main-key",
                'owner' => $actorUrl,
                'publicKeyPem' => $user->public_key,
            ],
        ];

        if ($avatar) {
            $actor['icon'] = [
                'type' => 'Image',
                'mediaType' => 'image/jpeg',
                'url' => $avatar,
            ];
        }

        return response()->json($actor, 200, [
            'Content-Type' => 'application/activity+json',
        ]);
    }

    public function outbox(Request $request, string $username): JsonResponse
    {
        $user = User::where('username', $username)->firstOrFail();
        $outboxUrl = url("/ap/users/{$user->username}/outbox");

        $posts = Post::where('user_id', $user->id)
            ->whereIn('visibility', [Visibility::PUBLIC, Visibility::UNLISTED])
            ->orderByDesc('published_at')
            ->cursorPaginate(20);

        $items = [];
        foreach ($posts as $post) {
            $note = $this->activityPubService->buildNote($post);
            $items[] = [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'id' => url("/ap/posts/{$post->id}").'#create',
                'type' => 'Create',
                'actor' => url("/ap/users/{$user->username}"),
                'published' => $post->published_at->toIso8601String(),
                'object' => $note,
            ];
        }

        $collection = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $outboxUrl,
            'type' => 'OrderedCollection',
            'totalItems' => Post::where('user_id', $user->id)
                ->whereIn('visibility', [Visibility::PUBLIC, Visibility::UNLISTED])
                ->count(),
            'orderedItems' => $items,
        ];

        if ($posts->nextCursor()) {
            $collection['next'] = $outboxUrl.'?cursor='.$posts->nextCursor()->encode();
        }

        return response()->json($collection, 200, [
            'Content-Type' => 'application/activity+json',
        ]);
    }

    public function followers(string $username): JsonResponse
    {
        $user = User::where('username', $username)->firstOrFail();

        return response()->json([
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => url("/ap/users/{$user->username}/followers"),
            'type' => 'OrderedCollection',
            'totalItems' => $user->activityPubFollowers()->count(),
        ], 200, [
            'Content-Type' => 'application/activity+json',
        ]);
    }

    public function note(string $postId): JsonResponse
    {
        $post = Post::with(['user', 'locationPost.location', 'transportPost.originStop.location', 'transportPost.destinationStop.location'])
            ->findOrFail($postId);

        if (! in_array($post->visibility, [Visibility::PUBLIC, Visibility::UNLISTED])) {
            abort(404);
        }

        $note = $this->activityPubService->buildNote($post);

        return response()->json(
            array_merge(['@context' => 'https://www.w3.org/ns/activitystreams'], $note),
            200,
            ['Content-Type' => 'application/activity+json']
        );
    }
}
