<?php

namespace App\Console\Commands;

use App\Jobs\FetchAirports;
use App\Jobs\ImportOsmExtractsJob;
use App\Jobs\StartupImportCountriesJob;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

#[Signature('app:startup-imports')]
#[Description('Import country data on startup')]
class StartupImports extends Command
{
    public function handle(): void
    {
        FetchAirports::dispatch();
        StartupImportCountriesJob::dispatch();

        // The Geofabrik import (unlike the imports above) is too expensive to
        // redo on every container restart, so it's dispatched only the first
        // time the app ever starts; after that it only runs on the schedule
        // configured in bootstrap/app.php (app.osm.geofabrik_import_schedule).
        if (! empty(config('app.osm.geofabrik_extracts')) && Cache::add('app:osm-geofabrik-import:first-run-dispatched', true)) {
            ImportOsmExtractsJob::dispatch();
        }
    }
}
