<?php

namespace App\Http\Middleware;

use App\Services\ActivityPubService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyHttpSignature
{
    public function __construct(
        private readonly ActivityPubService $activityPubService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->activityPubService->verifyHttpSignature($request)) {
            abort(401, 'Invalid HTTP Signature');
        }

        return $next($request);
    }
}
