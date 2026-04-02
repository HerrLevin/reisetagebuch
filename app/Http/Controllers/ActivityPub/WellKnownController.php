<?php

namespace App\Http\Controllers\ActivityPub;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WellKnownController extends Controller
{
    public function webfinger(Request $request): JsonResponse
    {
        $resource = $request->query('resource');
        if (! $resource || ! str_starts_with($resource, 'acct:')) {
            abort(400, 'Invalid resource parameter');
        }

        $parts = explode('@', substr($resource, 5));
        if (count($parts) !== 2) {
            abort(400, 'Invalid acct URI');
        }

        [$username, $domain] = $parts;

        if ($domain !== parse_url(config('app.url'), PHP_URL_HOST)) {
            abort(404);
        }

        $user = User::where('username', $username)->first();
        if (! $user) {
            abort(404);
        }

        $actorUrl = url("/ap/users/{$user->username}");

        return response()->json([
            'subject' => $resource,
            'links' => [
                [
                    'rel' => 'self',
                    'type' => 'application/activity+json',
                    'href' => $actorUrl,
                ],
            ],
        ], 200, ['Content-Type' => 'application/jrd+json']);
    }

    public function nodeInfoLinks(): JsonResponse
    {
        return response()->json([
            'links' => [
                [
                    'rel' => 'http://nodeinfo.diaspora.software/ns/schema/2.0',
                    'href' => url('/nodeinfo/2.0'),
                ],
            ],
        ]);
    }
}
