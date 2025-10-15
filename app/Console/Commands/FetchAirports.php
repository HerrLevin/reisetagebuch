<?php

namespace App\Console\Commands;

use App\Jobs\FetchAirports as FetchAirportsJob;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class FetchAirports extends Command
{
    protected $signature = 'app:fetch-airports';

    protected $description = 'Fetch airports from https://ourairports.com/data/ and store them in the database';

    public function handle(): int
    {
        $this->info('Fetching airports in Queue...');

        FetchAirportsJob::dispatch();

        return CommandAlias::SUCCESS;
    }
}
