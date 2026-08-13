<?php

namespace App\Console\Commands;

use App\Jobs\FetchAirports;
use App\Jobs\StartupImportCountriesJob;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:startup-imports')]
#[Description('Import country data on startup')]
class StartupImports extends Command
{
    public function handle(): void
    {
        FetchAirports::dispatch();
        StartupImportCountriesJob::dispatch();
    }
}
