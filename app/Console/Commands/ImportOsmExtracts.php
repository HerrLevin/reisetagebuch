<?php

namespace App\Console\Commands;

use App\Jobs\ImportOsmExtractJob;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ImportOsmExtracts extends Command
{
    protected $signature = 'app:import-osm-extracts {extracts?* : Local .osm.pbf file paths or URLs (e.g. a Geofabrik download link); defaults to app.osm.geofabrik_extracts}';

    protected $description = 'Queue an import of Locations from Geofabrik .osm.pbf extracts instead of live Overpass queries';

    public function handle(): int
    {
        $extracts = $this->argument('extracts') ?: config('app.osm.geofabrik_extracts');

        if (empty($extracts)) {
            $this->error('No extracts given and none configured in app.osm.geofabrik_extracts.');

            return CommandAlias::FAILURE;
        }

        foreach ($extracts as $extract) {
            ImportOsmExtractJob::dispatch($extract);
        }
        $this->info(sprintf('Queued %d ImportOsmExtractJob(s) on the data-import queue.', count($extracts)));

        return CommandAlias::SUCCESS;
    }
}
