<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Dto\RemoteFollowDto;
use App\Dto\RemoteFollowerDto;
use App\Http\Controllers\Backend\ActivityPubFederationBackend;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class ActivityPubFederationController extends Controller
{
    public function __construct(
        private readonly ActivityPubFederationBackend $backend,
    ) {
        parent::__construct();
    }

    public function resolve(Request $request): JsonResponse
    {
        $handle = ltrim(trim($request->query('handle', '')), '@');

        if (! $handle || ! str_contains($handle, '@')) {
            return response()->json(['error' => 'Invalid handle'], 422);
        }

        $profile = $this->backend->resolveHandle($this->auth->user()->id, $handle);

        if ($profile === null) {
            return response()->json(['error' => 'Actor not found'], 404);
        }

        return response()->json($profile);
    }

    public function following(): JsonResponse
    {
        return response()->json(
            $this->backend->listFollowing($this->auth->user()->id)
        );
    }

    #[OA\Get(
        path: '/users/{userId}/activitypub/followers',
        operationId: 'getUserActivityPubFollowers',
        description: 'Return the remote (ActivityPub) actors that follow a user',
        summary: 'Get remote followers',
        tags: ['Follows'],
        parameters: [new OA\Parameter(name: 'userId', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(
                response: 200,
                description: Controller::OA_DESC_SUCCESS,
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: RemoteFollowerDto::class))
            ),
        ]
    )]
    public function followersForUser(string $userId): JsonResponse
    {
        return response()->json($this->backend->listFollowers($userId));
    }

    #[OA\Get(
        path: '/users/{userId}/activitypub/following',
        operationId: 'getUserActivityPubFollowing',
        description: 'Return the remote (ActivityPub) actors a user follows',
        summary: 'Get remote following',
        tags: ['Follows'],
        parameters: [new OA\Parameter(name: 'userId', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(
                response: 200,
                description: Controller::OA_DESC_SUCCESS,
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: RemoteFollowDto::class))
            ),
        ]
    )]
    public function followingForUser(string $userId): JsonResponse
    {
        return response()->json($this->backend->listFollowing($userId));
    }

    public function follow(Request $request): Response
    {
        $request->validate(['actor_id' => ['required', 'string', 'url']]);

        $this->backend->follow($this->auth->user()->id, (string) $request->string('actor_id'));

        return response(null, 204);
    }

    public function unfollow(Request $request): Response
    {
        $request->validate(['actor_id' => ['required', 'string']]);

        $this->backend->unfollow($this->auth->user()->id, (string) $request->string('actor_id'));

        return response(null, 204);
    }
}
