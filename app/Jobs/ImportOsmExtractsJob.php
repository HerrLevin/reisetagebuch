<?php

namespace App\Jobs;

use App\Services\Osm\OsmGeofabrikImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Runs the configured Geofabrik extracts (config('app.osm.geofabrik_extracts'))
 * through OsmGeofabrikImportService. Scheduled weekly in bootstrap/app.php and
 * dispatched once on first startup by App\Console\Commands\StartupImports.
 */
class ImportOsmExtractsJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 60 * 60 * 6;

    // must stay >= $timeout: Redis's queue 'retry_after' (config/queue.php,
    // default 90s) otherwise requeues this still-running job as "lost" long
    // before it's actually done, so the next pickup fails immediately with
    // MaxAttemptsExceededException (tries: 1 in config/horizon.php).
    public int $retryAfter = 60 * 60 * 6 + 60;

    /**
     * @param  array<int, string>|null  $extracts  defaults to config('app.osm.geofabrik_extracts') when null
     */
    public function __construct(private readonly ?array $extracts = null)
    {
        $this->queue = 'data-import';
    }

    public function handle(OsmGeofabrikImportService $importService): void
    {
        foreach ($this->extracts ?? config('app.osm.geofabrik_extracts') as $extract) {
            try {
                $importService->import($extract);
            } catch (Throwable $e) {
                Log::error('ImportOsmExtractsJob: failed to import extract', [
                    'extract' => $extract,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
