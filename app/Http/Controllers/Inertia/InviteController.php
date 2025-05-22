<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Backend\InviteController as InviteBackend;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInviteCodeRequest;
use App\Models\Invite;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class InviteController extends Controller
{
    private InviteBackend $backend;

    public function __construct(InviteBackend $inviteBackend)
    {
        $this->backend = $inviteBackend;
    }

    public function index(): Response
    {
        $this->authorize('create', Invite::class);
        $invites = $this->backend->index(auth()->user()->id);

        return Inertia::render(
            'Invites',
            [
                'invites' => $invites,
            ]
        );
    }

    public function store(StoreInviteCodeRequest $request): RedirectResponse
    {
        $this->backend->store(auth()->user()->id, $request->input('expires_at'));

        return redirect()->route('invites.index')->with('success', 'Invite code created successfully.');
    }

    public function destroy(string $inviteCode): RedirectResponse
    {
        $this->backend->destroy($inviteCode);

        return redirect()->route('invites.index')->with('success', 'Invite code deleted successfully.');
    }
}
