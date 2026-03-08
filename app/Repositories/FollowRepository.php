<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Http\Resources\UserDto;
use App\Hydrators\UserHydrator;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FollowRepository
{
    private UserHydrator $userHydrator;

    public function __construct(?UserHydrator $userHydrator = null)
    {
        $this->userHydrator = $userHydrator ?? new UserHydrator;
    }

    /**
     * @return UserDto[]
     *
     * @throws ModelNotFoundException<User>
     */
    public function getFollowings(string $userId): array
    {
        $follows = User::findOrFail($userId)->followings()->with('targetUser')->get()->pluck('targetUser');

        return $follows->map(fn (User $user) => $this->userHydrator->modelToDto($user))->toArray();
    }

    /**
     * @return UserDto[]
     *
     * @throws ModelNotFoundException<User>
     */
    public function getFollowers(string $userId): array
    {
        $followers = User::findOrFail($userId)->followers()->with('originUser')->get()->pluck('originUser');

        return $followers->map(fn (User $user) => $this->userHydrator->modelToDto($user))->toArray();
    }

    /**
     * @throws ModelNotFoundException<User>
     */
    public function createFollow(string $originUserId, string $targetUserId): void
    {
        $originUser = User::findOrFail($originUserId);
        $targetUser = User::findOrFail($targetUserId);
        Follow::create([
            'origin_user_id' => $originUser->id,
            'target_user_id' => $targetUser->id,
        ]);
    }

    public function deleteFollow(string $originUserId, string $targetUserId): void
    {
        $originUser = User::findOrFail($originUserId);
        $targetUser = User::findOrFail($targetUserId);
        Follow::where('origin_user_id', $originUser->id)
            ->where('target_user_id', $targetUser->id)
            ->delete();
    }
}
