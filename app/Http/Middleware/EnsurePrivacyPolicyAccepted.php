<?php

namespace App\Http\Middleware;

use App\Dto\ErrorDto;
use App\Repositories\PrivacyPolicyRepository;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

readonly class EnsurePrivacyPolicyAccepted
{
    public function __construct(
        private PrivacyPolicyRepository $repository
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $current = $this->repository->currentModel();
        $user = $request->user();

        if (! $current || ! $user || $this->repository->hasAccepted($user, $current)) {
            return $next($request);
        }

        return response()->json(
            new ErrorDto(
                message: 'You must accept the current privacy policy before continuing.',
                code: 'privacyPolicyRequired',
            ),
            403
        );
    }
}
