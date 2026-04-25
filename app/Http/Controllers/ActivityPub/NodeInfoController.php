<?php

namespace App\Http\Controllers\ActivityPub;

use App\Enums\Visibility;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class NodeInfoController extends Controller
{
    public function nodeInfo20(): JsonResponse
    {
        $totalUsers = User::count();
        $activeMonth = User::where('updated_at', '>=', now()->subMonth())->count();
        $activeHalfYear = User::where('updated_at', '>=', now()->subMonths(6))->count();
        $localPosts = Post::where('visibility', Visibility::PUBLIC)->count();

        $links = [
            'version' => '2.0',
            'software' => [
                'name' => 'reisetagebuch',
                'version' => config('app.version'),
            ],
            'protocols' => [
                'activitypub',
            ],
            'services' => [
                'outbound' => [],
                'inbound' => [],
            ],
            'usage' => [
                'users' => [
                    'total' => $totalUsers,
                    'activeMonth' => $activeMonth,
                    'activeHalfyear' => $activeHalfYear,
                ],
                'localPosts' => $localPosts,
            ],
            'openRegistration' => config('app.registration'),
        ];

        return response()->json($links);
    }
}
