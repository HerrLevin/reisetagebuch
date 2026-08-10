<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\ActivityPubFollowRequest;

class ActivityPubFollowRequestRepository
{
    public function findForUser(string $userId, string $requestId): ActivityPubFollowRequest
    {
        return ActivityPubFollowRequest::where('followed_user_id', $userId)
            ->findOrFail($requestId);
    }

    public function delete(ActivityPubFollowRequest $followRequest): void
    {
        $followRequest->delete();
    }
}
