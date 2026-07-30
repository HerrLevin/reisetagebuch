<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\ActivityPubFollower;
use Illuminate\Support\Collection;

class ActivityPubFollowerRepository
{
    public function listForUser(string $userId): Collection
    {
        return ActivityPubFollower::where('followed_user_id', $userId)
            ->with('actor')
            ->orderByDesc('created_at')
            ->get();
    }
}
