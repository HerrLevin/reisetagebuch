<?php

namespace App\Http\Controllers\Backend;

use App\Dto\AppConfigurationDto;
use App\Dto\FeatureFlag;
use App\Enums\Feature;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

class AppConfigurationBackend extends Controller
{
    public function index(): AppConfigurationDto
    {
        return new AppConfigurationDto(
            appName: config('app.name'),
            appVersion: config('app.version'),
            featureFlags: $this->getFeatureFlags(),
            isDebug: (bool) config('app.debug'),
            environment: (string) config('app.env'),
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

        $featureFlags[] = new FeatureFlag(
            name: Feature::HORIZON,
            enabled: Route::has('horizon.index'),
        );

        // Telescope's routes only exist when it is enabled, see AppServiceProvider.
        $featureFlags[] = new FeatureFlag(
            name: Feature::TELESCOPE,
            enabled: Route::has('telescope'),
        );

        return $featureFlags;
    }
}
