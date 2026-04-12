<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Laravel\Passport\Console\KeysCommand;

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
