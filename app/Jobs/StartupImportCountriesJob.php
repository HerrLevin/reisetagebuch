<?php

namespace App\Jobs;

use App\Console\Commands\BackfillCountryData;
use App\Console\Commands\ImportCountries;
use Artisan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class StartupImportCountriesJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Artisan::call(ImportCountries::class);
        Artisan::call(BackfillCountryData::class);
    }
}
