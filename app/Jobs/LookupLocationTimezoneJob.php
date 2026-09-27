<?php

namespace App\Jobs;

use App\Models\Location;
use App\Services\TimeZoneLookupService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class LookupLocationTimezoneJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $locationId
    ) {}

    public function handle(TimeZoneLookupService $timeZoneLookupService): void
    {
        $location = Location::find($this->locationId);

        if ($location === null || $location->timezone !== null) {
            return;
        }

        $timezone = $timeZoneLookupService->lookup(
            $location->location->getLatitude(),
            $location->location->getLongitude(),
        );

        if ($timezone === null) {
            return;
        }

        $location->timezone = $timezone;
        $location->save();
    }
}
