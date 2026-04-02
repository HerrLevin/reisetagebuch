<?php

namespace App\Http\Controllers\Api;

use App\Enums\ActivityPubInteractionType;
use App\Http\Controllers\Controller;
use App\Models\ActivityPubInteraction;
use Illuminate\Http\JsonResponse;

class ActivityPubInteractionController extends Controller
{
    public function index(string $postId): JsonResponse
    {
        $interactions = ActivityPubInteraction::where('post_id', $postId)->get();

        $likes = $interactions->where('type', ActivityPubInteractionType::LIKE)->count();
        $boosts = $interactions->where('type', ActivityPubInteractionType::BOOST)->count();
        $replies = $interactions->where('type', ActivityPubInteractionType::REPLY)
            ->map(fn (ActivityPubInteraction $interaction) => [
                'id' => $interaction->id,
                'actorUsername' => $interaction->actor_username,
                'actorDisplayName' => $interaction->actor_display_name,
                'actorAvatar' => $interaction->actor_avatar,
                'actorInstance' => $interaction->actor_instance,
                'content' => $interaction->content,
                'remoteUrl' => $interaction->remote_url,
                'createdAt' => $interaction->created_at->toIso8601String(),
            ])
            ->values();

        return response()->json([
            'likes' => $likes,
            'boosts' => $boosts,
            'replies' => $replies,
        ]);
    }
}
