<?php

namespace App\Jobs;

use App\Services\Osm\OsmGeofabrikImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Imports a single Geofabrik extract. Split out per extract (dispatched by
 * App\Console\Commands\ImportOsmExtracts) so one country's failure only
 * fails that job and can be retried on its own in Horizon, instead of
 * derailing/re-running the whole batch.
 */
class ImportOsmExtractJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 60 * 60 * 6;

    // must stay >= $timeout: Redis's queue 'retry_after' (config/queue.php,
    // default 90s) otherwise requeues this still-running job as "lost" long
    // before it's actually done, so the next pickup fails immediately with
    // MaxAttemptsExceededException (tries: 1 in config/horizon.php).
    public int $retryAfter = 60 * 60 * 6 + 60;

    public function __construct(public readonly string $extract)
    {
        $this->queue = 'data-import';
    }

    public function handle(OsmGeofabrikImportService $importService): void
    {
        $importService->import($this->extract);
    }
}
