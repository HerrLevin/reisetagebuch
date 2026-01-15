<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use App\Models\Invite;
use Inertia\Inertia;
use Inertia\Response;

class InviteController extends Controller
{
    public function index(): Response
    {
        $this->authorize('create', Invite::class);

        return Inertia::render('Invites');
    }
}
