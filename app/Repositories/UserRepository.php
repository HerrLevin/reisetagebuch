<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Http\Resources\UserDto;
use App\Hydrators\UserHydrator;
use App\Models\User;

class UserRepository
{
    private UserHydrator $userHydrator;

    public function __construct(?UserHydrator $userHydrator = null)
    {
        $this->userHydrator = $userHydrator ?? new UserHydrator;
    }

    public function getUserByUsername(string $username): UserDto
    {
        $user = User::with('profile')->where('username', $username)->firstOrFail();

        return $this->userHydrator->modelToDto($user);
    }

    public function getUserById(string $userId): UserDto
    {
        $user = User::with('profile')->findOrFail($userId);

        return $this->userHydrator->modelToDto($user);
    }

    public function updateAvatar(User $user, ?string $avatarPath): UserDto
    {
        if (! $user->profile) {
            $user->profile()->create(['avatar' => $avatarPath]);
        } else {
            $user->profile()->update(['avatar' => $avatarPath]);
        }

        $user->load('profile');

        return $this->userHydrator->modelToDto($user);
    }

    public function updateHeader(User $user, ?string $headerPath): UserDto
    {
        if (! $user->profile) {
            $user->profile()->create(['header' => $headerPath]);
        } else {
            $user->profile()->update(['header' => $headerPath]);
        }

        $user->load('profile');

        return $this->userHydrator->modelToDto($user);
    }

    public function updateUser(User $user, string $name, ?string $bio, ?string $website = null): UserDto
    {
        $user->update([
            'name' => $name,
        ]);

        $profileData = [
            'bio' => empty($bio) ? null : $bio,
            'website' => empty($website) ? null : $website,
        ];

        if (! $user->profile) {
            $user->profile()->create($profileData);
        } else {
            $user->profile()->update($profileData);
        }

        $user->load('profile');

        return $this->userHydrator->modelToDto($user);
    }
}
