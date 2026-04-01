<?php

namespace App\Http\Controllers\ActivityPub;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class NodeInfoController extends Controller
{
    public function nodeInfo20(): JsonResponse
    {
        $links = [
            'version' => '2.0',
            'software' => [
                'name' => 'reisetabebuch',
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
                    'total' => 0, // todo: provide real statistics
                    'activeMonth' => 0,
                    'activeHalfyear' => 0,
                ],
                'localPosts' => 0,
            ],
            'openRegistration' => config('app.registration'),
        ];

        return response()->json($links);
    }
}
