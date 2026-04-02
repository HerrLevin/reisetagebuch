<?php

namespace App\Http\Controllers\ActivityPub;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class NodeInfoController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json([
            'version' => '2.0',
            'software' => [
                'name' => 'reisetagebuch',
                'version' => config('app.version', '1.0.0'),
            ],
            'protocols' => ['activitypub'],
            'usage' => [
                'users' => [
                    'total' => User::count(),
                ],
                'localPosts' => Post::count(),
            ],
            'openRegistrations' => false,
        ]);
    }
}
