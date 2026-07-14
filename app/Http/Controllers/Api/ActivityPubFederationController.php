<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Backend\ActivityPubFederationBackend;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
