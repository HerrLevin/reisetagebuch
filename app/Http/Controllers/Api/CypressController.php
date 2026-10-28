<?php

namespace App\Http\Controllers\Api;

use App\Dto\TokenResponseDto;
use App\Http\Controllers\Backend\AuthBackend;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Laravel\Passport\Console\KeysCommand;
use Throwable;

class CypressController extends Controller
{
    public function seed(Request $request): JsonResponse
    {
        $this->auth($request);

        Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
        Artisan::call(KeysCommand::class);

        return response()->json(['message' => 'Database seeded successfully.']);
    }

    public function reset(Request $request): JsonResponse
    {
        $this->auth($request);

        Artisan::call('migrate:fresh', ['--seed' => false, '--force' => true]);
        Artisan::call(KeysCommand::class);

        return response()->json(['message' => 'Database reset successfully.']);
    }

    /**
     * Creates a user and returns a personal access token, so e2e tests can skip the OAuth flow.
     *
     * @throws Throwable
     */
    public function register(Request $request, AuthBackend $backend): JsonResponse
    {
        $this->auth($request);

        $user = $backend->registerUser($request->only('name', 'username', 'email', 'password'));
        $token = $user->createToken('cypress', ['*']);

        return response()->json(
            new TokenResponseDto(
                token: $token->accessToken,
                user: $backend->getAuthenticatedUser($user),
                expiresAt: Carbon::now()->addSeconds($token->expiresIn)
            ),
            201
        );
    }

    private function auth(Request $request): void
    {
        if (! config('app.testing.cypress')) {
            abort(403, 'Cypress endpoints are only available in local/testing environments.');
        }

        if ($request->header('X-Cypress-Token') !== config('app.testing.cypress_token')) {
            abort(403, 'Invalid Cypress token.');
        }
    }
}
