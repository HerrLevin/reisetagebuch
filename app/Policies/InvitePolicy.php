<?php

namespace App\Policies;

use App\Models\Invite;
use App\Models\User;

class InvitePolicy
{
    public function create(User $user): bool
    {
        $whitelist = config('app.invite.whitelist');
        return empty($whitelist) || in_array($user->id, $whitelist);
    }

    public function delete(User $user, Invite $inviteCode): bool
    {
        return $user->id === $inviteCode->user_id;
    }
}
