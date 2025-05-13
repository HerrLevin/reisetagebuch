<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInviteCodeRequest;
use App\Models\Invite;
use App\Repositories\InviteRepository;

class InviteController extends Controller
{
    private InviteRepository $inviteRepository;

    public function __construct(InviteRepository $inviteRepository)
    {
        $this->inviteRepository = $inviteRepository;
    }

    public function index(): array
    {
        $this->authorize('create', Invite::class);

        return $this->inviteRepository->getAllInvitesForUser(auth()->user()->id);
    }

    public function store(StoreInviteCodeRequest $request): void
    {
        $this->inviteRepository->createInvite(
            auth()->user()->id,
            $request->input('expires_at')
        );
    }

    public function destroy(string $inviteCode): void
    {
        $invite = $this->inviteRepository->getInviteByIdOrFail($inviteCode);
        $this->authorize('delete', $invite);

        $invite->delete();
    }
}
