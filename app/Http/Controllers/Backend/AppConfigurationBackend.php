<?php

namespace App\Http\Controllers\Backend;

use App\Dto\AppConfigurationDto;
use App\Dto\FeatureFlag;
use App\Enums\Feature;
use App\Http\Controllers\Controller;

class AppConfigurationBackend extends Controller
{
    public function index(): AppConfigurationDto
    {
        return new AppConfigurationDto(
            appName: config('app.name'),
            featureFlags: $this->getFeatureFlags(),
        );
    }

    private function getFeatureFlags(): array
    {
        $featureFlags = [];

        $featureFlags[] = new FeatureFlag(
            name: Feature::REGISTRATION,
            enabled: config('app.registration'),
        );

        $featureFlags[] = new FeatureFlag(
            name: Feature::INVITE,
            enabled: config('app.invite.enabled'),
        );

        return $featureFlags;
    }
}
