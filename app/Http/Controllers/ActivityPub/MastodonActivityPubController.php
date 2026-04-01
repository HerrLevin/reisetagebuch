<?php

namespace App\Http\Controllers\ActivityPub;

use ActivityPhp\Type;
use App\Enums\TransportMode;
use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\Post;
use App\Models\User;
use App\Services\ActivityPubService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MastodonActivityPubController extends Controller
{
    private ActivityPubService $activityPubService;

    public function __construct(ActivityPubService $activityPubService)
    {
        $this->activityPubService = $activityPubService;
    }

    public function actor(Request $request, string $username): JsonResponse
    {
        $user = User::where('username', $username)->first();
        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $person = Type::create('Person', [
            'id' => route('ap.actor', ['username' => $user->username]),
            'preferredUsername' => $user->username,
            'name' => $user->name,
            'inbox' => route('ap.inbox', ['username' => $user->username]),
            'outbox' => route('ap.outbox', ['username' => $user->username]),
            'publicKey' => [
                'id' => route('ap.actor', ['username' => $user->username.'#main-key']),
                'owner' => route('ap.actor', ['username' => $user->username]),
                'publicKeyPem' => $user->public_key,
            ],
        ]);
        $person->set('@context', [
            'https://www.w3.org/ns/activitystreams',
            'https://w3id.org/security/v1',
        ]);

        return response()->json($person->toArray())->header('Content-Type', 'application/activity+json');
    }

    public function outbox(Request $request, string $username): JsonResponse
    {
        $user = User::where('username', $username)->first();
        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if (! $request->has('page') && ! $request->has('cursor')) {
            // First page request
            $collection = Type::create('OrderedCollection', [
                'id' => route('ap.outbox', ['username' => $user->username]),
                'totalItems' => $user->posts()->count(),
                'first' => route('ap.outbox', ['username' => $user->username, 'page' => 'true']),
            ]);
            $collection->set('@context', 'https://www.w3.org/ns/activitystreams');

            return response()->json($collection->toArray())->header('Content-Type', 'application/activity+json');
        }

        $posts = $user->posts()->orderBy('created_at', 'desc')->orderBy('id', 'desc')->cursorPaginate(5);

        $items = [];
        foreach ($posts as $post) {
            $note = Type::create('Note', [
                'id' => route('ap.object', ['id' => $post->id]),
                'published' => $post->created_at->toISOString(),
                'attributedTo' => route('ap.actor', ['username' => $post->user->username]),
                'content' => $post->body,
                'to' => ['https://www.w3.org/ns/activitystreams#Public'],
            ]);

            $create = Type::create('Create', [
                'id' => route('ap.object', ['id' => $post->id]),
                'actor' => route('ap.actor', ['username' => $post->user->username]),
                'published' => $post->created_at->toISOString(),
                'to' => ['https://www.w3.org/ns/activitystreams#Public'],
                'object' => $note,
            ]);

            $items[] = $create;
        }

        $page = Type::create('OrderedCollectionPage', [
            'id' => route('ap.outbox', ['username' => $user->username]),
            'orderedItems' => $items,
            'partOf' => route('ap.outbox', ['username' => $user->username]),
        ]);

        if ($posts->nextPageUrl()) {
            $page->set('next', $posts->nextPageUrl());
        }
        if ($posts->previousPageUrl()) {
            $page->set('prev', $posts->previousPageUrl());
        }

        $page->set('@context', 'https://www.w3.org/ns/activitystreams');

        return response()->json($page->toArray())->header('Content-Type', 'application/activity+json');
    }

    public function inbox(Request $request, string $username): JsonResponse
    {
        $user = User::where('username', $username)->first();
        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $activity = $request->json()->all();

        if ($activity['type'] === 'Follow') {
            // Handle follow
            $followerActorId = $activity['actor'];
            $followedActorId = $activity['object'];

            // Verify it's following this user
            if ($followedActorId !== route('ap.actor', ['username' => $user->username])) {
                return response()->json(['error' => 'Invalid follow object'], 400);
            }

            // Create follow record
            Follow::firstOrCreate([
                'follower_actor_id' => $followerActorId,
                'followed_user_id' => $user->id,
            ]);

            // Send Accept activity
            $this->sendAccept($user, $activity);

            return response()->json('', 202);
        }

        // For other activities, just accept
        return response()->json('', 202);
    }

    public function postObject(Request $request, string $id): JsonResponse
    {
        $post = Post::find($id);
        if (! $post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        $body = $post->body ?? '';

        if ($post->transportPost) {
            $emoji = TransportMode::tryFrom($post->transportPost->transportTrip->mode)?->getEmoji();
            $line = $post->transportPost->transportTrip->line_name;
            $origin = $post->transportPost->originStop->location->name;
            $destination = $post->transportPost->destinationStop->location->name;
            $duration = round($post->transportPost->duration / 60);
            $distance = round($post->transportPost->distance / 1000, 1);
            $body = "<p>$emoji<strong>$line</strong> · $origin → $destination<br>🕐 $duration min · $distance km</p>";

            $body = $post->body ? nl2br(e($post->body)).$body : $body;
        }

        if ($post->locationPost) {

            $name = $post->locationPost->location->name;
            $body = "<p>📍<strong>$name</strong></p>";

            $body = $post->body ? nl2br(e($post->body)).$body : $body;
        }

        $note = Type::create('Note', [
            'id' => route('ap.object', ['id' => $id]),
            'published' => $post->created_at->toISOString(),
            'attributedTo' => route('ap.actor', ['username' => $id]),
            'content' => $body,
            'to' => ['https://www.w3.org/ns/activitystreams#Public'],
        ]);
        $note->set('@context', 'https://www.w3.org/ns/activitystreams');

        return response()->json($note->toArray())->header('Content-Type', 'application/activity+json');
    }

    private function sendAccept(User $user, array $followActivity): void
    {
        $followerActorId = $followActivity['actor'];

        // Create Accept activity
        $accept = Type::create('Accept', [
            'id' => url('/activities/'.uniqid()),
            'actor' => route('ap.actor', ['username' => $user->username]),
            'object' => $followActivity,
        ]);
        $accept->set('@context', 'https://www.w3.org/ns/activitystreams');

        $this->activityPubService->deliverActivity($user, $followerActorId, $accept->toArray());
    }
}
