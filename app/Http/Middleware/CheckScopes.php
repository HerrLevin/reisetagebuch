<?php

namespace App\Http\Middleware;

use App\Enums\Scopes\Scope;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Exceptions\MissingScopeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Requires the given granular scopes, each satisfied by itself or its parent
 * (e.g. "read" covers "read:statuses"). Guests pass, so it also works on
 * routes where authentication is optional; protected routes add auth:api.
 */
class CheckScopes
{
    public function handle(Request $request, Closure $next, string ...$scopes): Response
    {
        $token = Auth::guard('api')->user()?->currentAccessToken();

        foreach ($scopes as $scope) {
            $parent = Scope::parentOf($scope);
            if ($token && $token->cant($scope) && ! ($parent && $token->can($parent->value))) {
                throw new MissingScopeException($scope);
            }
        }

        return $next($request);
    }
}
