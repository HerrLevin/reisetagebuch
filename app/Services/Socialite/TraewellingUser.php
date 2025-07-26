<?php

namespace App\Services\Socialite;

use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

class TraewellingUser extends SocialiteUser
{
    public function logout(): void
    {
        $provider = Socialite::driver('traewelling');
        $provider->logout($this->token);
    }
}
