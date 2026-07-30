<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Dto\PrivacyPolicyDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\PrivacyPolicyStoreRequest;
use App\Models\PrivacyPolicy;
use App\Models\User;
use App\Repositories\PrivacyPolicyRepository;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class PrivacyPolicyBackend extends Controller
{
    public function __construct(
        private readonly PrivacyPolicyRepository $privacyPolicyRepository
    ) {}

    public function show(?User $user): PrivacyPolicyDto
    {
        return $this->privacyPolicyRepository->getCurrent($user) ?? abort(404);
    }

    public function showUpcoming(?User $user): PrivacyPolicyDto
    {
        return $this->privacyPolicyRepository->getUpcoming($user) ?? abort(404);
    }

    public function store(PrivacyPolicyStoreRequest $request): PrivacyPolicyDto
    {
        return $this->privacyPolicyRepository->store(
            content: $request->input('content'),
            validFrom: Carbon::parse($request->input('validFrom')),
        );
    }

    public function accept(User $user, PrivacyPolicy $policy): PrivacyPolicyDto
    {
        $current = $this->privacyPolicyRepository->currentModel();
        $upcoming = $this->privacyPolicyRepository->upcomingModel();

        if ($policy->id !== $current?->id && $policy->id !== $upcoming?->id) {
            throw ValidationException::withMessages([
                'privacy_policy' => 'Only the current or upcoming privacy policy version can be accepted.',
            ]);
        }

        return $this->privacyPolicyRepository->accept($user, $policy);
    }
}
