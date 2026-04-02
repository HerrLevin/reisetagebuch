<?php

namespace App\Http\Controllers\ActivityPub;

use ActivityPhp\Type;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserDto;
use App\Models\ActivityPubFollower;
use App\Models\User;
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
        private readonly PostRepository $postRepository
    ) {}

    private function checkHeader(Request $request)
    {
        return ! str_contains($request->header('accept'), 'application/ld+json');
    }

    public function actor(Request $request, string $username): JsonResponse|RedirectResponse
    {
        if ($this->checkHeader($request)) {
            return redirect()->away(url('/profile/'.$username));
        }
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
        Log::info('actor response', $person->toArray());

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
                'id' => route('ap.post-object', ['id' => $post->id]),
                'published' => $post->created_at->toISOString(),
                'attributedTo' => route('ap.actor', ['username' => $post->user->username]),
                'content' => $post->body,
                'to' => ['https://www.w3.org/ns/activitystreams#Public'],
            ]);

            $create = Type::create('Create', [
                'id' => route('ap.post-object', ['id' => $post->id]),
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
        Log::info('getting inbox request', [$request->all()]);
        $user = $this->userRepository->getUserByUsername($username);

        $activity = $request->json()->all();

        if ($activity['type'] === 'Follow') {
            return $this->handleFollow($activity, $user);
        }
        if ($activity['type'] === 'Undo' && $activity['object']['type'] === 'Follow') {
            return $this->handleUndoFollow($activity, $user);
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

        $follow = ActivityPubFollower::firstOrCreate([
            'follower_actor_id' => $followerActorId,
            'followed_user_id' => $user->id,
        ]);

        $this->sendAccept($user, $activity, $follow->id);

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

        $delete = ActivityPubFollower::where([
            'follower_actor_id' => $followerActorId,
            'followed_user_id' => $user->id,
        ])->firstOrFail();

        $delete->delete();

        $this->sendAccept($user, $activity, $followedActorId);

        return response()->json('', 202);
    }

    public function postObject(Request $request, string $id): RedirectResponse|JsonResponse
    {
        if ($this->checkHeader($request)) {
            return redirect()->away(url('/posts/'.$id));
        }
        $post = $this->postRepository->getById($id);

        $note = Type::create('Note', [
            'id' => route('ap.post-object', ['id' => $id]),
            'published' => $post->publishedAt,
            'attributedTo' => route('ap.actor', ['username' => $id]),
            'content' => $post->getBody() ?? '',
            'to' => ['https://www.w3.org/ns/activitystreams#Public'],
        ]);
        $note->set('@context', 'https://www.w3.org/ns/activitystreams');

        return response()->json($note->toArray())->header('Content-Type', 'application/activity+json');
    }

    private function sendAccept(UserDto $user, array $followActivity, ?string $id): void
    {
        $followerActorId = $followActivity['actor'];

        // Create Accept activity
        $accept = Type::create('Accept', [
            'id' => route('ap.activity', ['id' => $id]),
            'actor' => route('ap.actor', ['username' => $user->username]),
            'object' => $followActivity,
        ]);
        $accept->set('@context', 'https://www.w3.org/ns/activitystreams');

        $this->activityPubService->deliverActivity($user, $followerActorId, $accept->toArray());
    }
}
