<?php

namespace App\Repositories;

use App\Hydrators\InviteHydrator;
use App\Models\Invite;

class InviteRepository
{
    private InviteHydrator $inviteHydrator;

    public function __construct(?InviteHydrator $inviteHydrator = null)
    {
        $this->inviteHydrator = $inviteHydrator ?? new InviteHydrator;
    }

    public function getAllInvitesForUser(string $userId): array
    {
        $invites = Invite::where('user_id', $userId)
            ->orderByDesc('used_at')
            ->orderByDesc('created_at')
            ->get();

        return $invites->map(function ($invite) {
            return $this->inviteHydrator->modelToDto($invite);
        })->toArray();
    }

    public function getInviteByIdOrFail(string $inviteId): ?Invite
    {
        return Invite::where('id', $inviteId)->firstOrFail();
    }

    public function getInviteById(string $inviteId): ?Invite
    {
        return Invite::where('id', $inviteId)->first();
    }

    public function getAvailableInviteById(string $inviteId): ?Invite
    {
        return Invite::where('id', $inviteId)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());

            })
            ->whereNull('used_at')
            ->first();
    }

    public function createInvite(string $userId, ?string $expiresAt = null): Invite
    {
        return Invite::create([
            'user_id' => $userId,
            'expires_at' => $expiresAt,
        ]);
    }
}
