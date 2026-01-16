<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Resources\InviteDto;
use App\Repositories\InviteRepository;

class InviteController extends Controller
{
    private InviteRepository $inviteRepository;

    public function __construct(InviteRepository $inviteRepository)
    {
        $this->inviteRepository = $inviteRepository;
    }

    /**
     * @return InviteDto[]
     */
    public function index(string $userId): array
    {
        return $this->inviteRepository->getAllInvitesForUser($userId);
    }

    public function store(string $userId, ?string $expiresAt = null): void
    {
        $this->inviteRepository->createInvite(
            $userId,
            $expiresAt
        );
    }

    public function destroy(string $inviteCode): void
    {
        $invite = $this->inviteRepository->getInviteByIdOrFail($inviteCode);
        $this->authorize('delete', $invite);

        $invite->delete();
    }
}
