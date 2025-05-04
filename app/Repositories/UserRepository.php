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
        $this->userHydrator = $userHydrator ?? new UserHydrator();
    }

    public function getUserByUsername(string $username): UserDto
    {
        $user = User::where('username', $username)->firstOrFail();

        return $this->userHydrator->modelToDto($user);
    }
}
