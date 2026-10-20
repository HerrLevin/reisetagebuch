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
        StartupImportCountriesJob::dispatch();

        if (! empty(config('app.osm.geofabrik_extracts')) && Cache::add('app:osm-geofabrik-import:first-run-dispatched', true)) {
            ImportOsmExtractsJob::dispatch();
        }

        if (Cache::add('app:startup-imports:fetch-airports-dispatched', true)) {
            FetchAirports::dispatch();
        }
    }
}
