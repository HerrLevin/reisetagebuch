<?php

namespace App\Jobs;

use App\Repositories\LocationRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class BackfillLocationTimezones implements ShouldQueue
{
    use Queueable;

    public function handle(LocationRepository $locationRepository): void
    {
        $limit = (int) config('app.timeapi.backfill_limit');

        foreach ($locationRepository->getLocationsWithMissingTimezone($limit) as $location) {
            LookupLocationTimezoneJob::dispatch($location->id);
        }
    }
}
