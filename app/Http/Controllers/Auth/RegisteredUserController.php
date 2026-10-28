<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Backend\AuthBackend;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\InviteRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class RegisteredUserController extends Controller
{
    public function __construct(
        private readonly AuthBackend $backend,
        private readonly InviteRepository $inviteRepository,
    ) {}

    public function create(Request $request): View
    {
        $this->ensureRegistrationAvailable();

        return view('auth.register', ['invite' => $request->query('invite')]);
    }

    /**
     * @throws ValidationException
     * @throws Throwable
     */
    public function store(Request $request): RedirectResponse
    {
        $this->ensureRegistrationAvailable();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|alpha_dash|max:30|unique:'.User::class,
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'invite' => 'nullable|string|exists:invites,id',
        ]);

        $invite = null;
        if (! config('app.registration')) {
            $invite = $this->inviteRepository->getInviteById((string) $request->input('invite'));

            if (! $invite || $invite->used_at) {
                throw ValidationException::withMessages(['invite' => __('Invite code already used.')]);
            }
            if ($invite->expires_at && $invite->expires_at < now()) {
                throw ValidationException::withMessages(['invite' => __('Invite code expired.')]);
            }
        }

        $user = $this->backend->registerUser($validated, $invite);

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect()->intended('/home');
    }

    private function ensureRegistrationAvailable(): void
    {
        abort_unless(config('app.registration') || config('app.invite.enabled'), 403, 'Registration is disabled.');
    }
}
