<?php

namespace App\Jobs;

use App\Jobs\ActivityPub\PushLocationTimezoneChangeToMastodon;
use App\Models\Location;
use App\Repositories\LocationRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class LookupLocationTimezoneJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $locationId
    ) {}

    public function handle(LocationRepository $locationRepository): void
    {
        $location = Location::find($this->locationId);

        if ($location === null) {
            return;
        }

        if ($locationRepository->resolveTimezoneNow($location)) {
            PushLocationTimezoneChangeToMastodon::dispatch($location->id);
        }
    }
}
