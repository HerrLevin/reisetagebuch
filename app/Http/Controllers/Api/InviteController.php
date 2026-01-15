<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Backend\InviteController as Backend;
use App\Http\Requests\StoreInviteCodeRequest;
use App\Models\Invite;

class InviteController extends Controller
{
    private Backend $backend;

    public function __construct(Backend $backend)
    {
        $this->backend = $backend;
        parent::__construct();
    }

    public function index(): array
    {
        $this->authorize('create', Invite::class);

        return $this->backend->index($this->auth->user()->id);
    }

    public function store(StoreInviteCodeRequest $request): array
    {
        $this->authorize('create', Invite::class);

        $this->backend->store($this->auth->user()->id, $request->input('expires_at'));

        return ['success' => true];
    }

    public function destroy(string $inviteCode): array
    {
        $this->backend->destroy($inviteCode);

        return ['success' => true];
    }
}
