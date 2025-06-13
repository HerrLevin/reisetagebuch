<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\InviteRepository;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    private InviteRepository $inviteRepository;

    public function __construct(InviteRepository $inviteRepository)
    {
        $this->inviteRepository = $inviteRepository;
    }

    public function create(Request $request): Application|RedirectResponse|Response
    {
        if (!config('app.registration') && !config('app.invite.enabled')) {
            return redirect(route('login', absolute: false));
        }

        $invite = null;
        if (!config('app.registration') && config('app.invite.enabled')) {
            $invite = $request->invite ? $this->inviteRepository->getAvailableInviteById($request->invite) : null;

            if (empty($invite)) {
                abort(404, __('Invite code not found or expired.'));
            }
        }

        return Inertia::render(
            'Auth/Register',
            [
                'inviteCode' => $invite ? $invite->id : null,
                'invitedBy' => $invite ? $invite->createdBy->name : null,
            ]
        );
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        if (!config('app.registration') && !config('app.invite.enabled')) {
            return redirect(route('login', absolute: false));
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|alpha_dash|max:30|unique:' . User::class,
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'invite' => 'nullable|string|exists:invites,id',
        ]);

        $invite = null;
        if (!config('app.registration') && config('app.invite.enabled')) {
            $invite = $this->inviteRepository->getInviteById($request->invite);

            if ($invite->used_at) {
                throw ValidationException::withMessages([
                    'invite' => __('Invite code already used.'),
                ]);
            }
            if ($invite->expires_at && $invite->expires_at < now()) {
                throw ValidationException::withMessages([
                    'invite' => __('Invite code expired.'),
                ]);
            }
        }

        DB::beginTransaction();
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $user->profile()->create();
        $user->settings()->create();
        $invite?->update([
            'used_by' => $user->id,
            'used_at' => now(),
        ]);
        DB::commit();

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
