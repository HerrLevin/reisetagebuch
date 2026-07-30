<?php

namespace App\Repositories;

use App\Dto\PrivacyPolicyDto;
use App\Models\PrivacyPolicy;
use App\Models\User;
use Carbon\CarbonInterface;

class PrivacyPolicyRepository
{
    public function currentModel(): ?PrivacyPolicy
    {
        return PrivacyPolicy::query()
            ->where('valid_from', '<=', now())
            ->orderByDesc('valid_from')
            ->first();
    }

    public function upcomingModel(): ?PrivacyPolicy
    {
        return PrivacyPolicy::query()
            ->where('valid_from', '>', now())
            ->orderBy('valid_from')
            ->first();
    }

    public function getCurrent(?User $user): ?PrivacyPolicyDto
    {
        $policy = $this->currentModel();

        return $policy ? $this->toDto($policy, $user) : null;
    }

    public function getUpcoming(?User $user): ?PrivacyPolicyDto
    {
        $policy = $this->upcomingModel();

        return $policy ? $this->toDto($policy, $user) : null;
    }

    public function store(string $content, CarbonInterface $validFrom): PrivacyPolicyDto
    {
        $policy = PrivacyPolicy::query()->create([
            'content' => $content,
            'valid_from' => $validFrom,
        ]);

        return $this->toDto($policy, null);
    }

    public function accept(User $user, PrivacyPolicy $policy): PrivacyPolicyDto
    {
        $policy->acceptances()->firstOrCreate(
            ['user_id' => $user->id],
            ['accepted_at' => now()]
        );

        return $this->toDto($policy, $user);
    }

    public function hasAccepted(User $user, PrivacyPolicy $policy): bool
    {
        return $policy->acceptances()->where('user_id', $user->id)->exists();
    }

    private function toDto(PrivacyPolicy $policy, ?User $user): PrivacyPolicyDto
    {
        $acceptedAt = null;

        if ($user) {
            $acceptedAt = $policy->acceptances()
                ->where('user_id', $user->id)
                ->value('accepted_at');
        }

        return new PrivacyPolicyDto(
            id: $policy->id,
            content: $policy->content,
            validFrom: $policy->valid_from->toIso8601String(),
            acceptedAt: $acceptedAt?->toIso8601String(),
        );
    }
}
