<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Backend\AccountBackend;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    private AccountBackend $accountBackend;

    public function __construct(AccountBackend $accountController)
    {
        $this->accountBackend = $accountController;
    }

    public function edit(Request $request): Response
    {
        return Inertia::render('Settings/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $this->accountBackend->update($request);
        return Redirect::route('account.edit');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->accountBackend->destroy($request);

        return Redirect::to('/');
    }
}
