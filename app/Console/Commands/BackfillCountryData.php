<?php

namespace App\Console\Commands;

use App\Jobs\CalculateStatsForTransportPost;
use App\Models\Location;
use App\Models\TransportPost;
use App\Repositories\LocationRepository;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class BackfillCountryData extends Command
{
    protected $signature = 'app:backfill-country-data {--chunk=500}';

    protected $description = 'Backfill country_code on existing locations and transited_country_codes on existing transport posts';

    public function handle(LocationRepository $locationRepository): int
    {
        $chunkSize = (int) $this->option('chunk');

        $this->info('Backfilling location country codes...');
        $bar = $this->output->createProgressBar(Location::whereNull('country_code')->count());
        Location::whereNull('country_code')->chunkById($chunkSize, function ($locations) use ($bar, $locationRepository) {
            foreach ($locations as $location) {
                $location->update(['country_code' => $locationRepository->resolveCountryCode($location->location)]);
                $bar->advance();
            }
        });
        $bar->finish();
        $this->newLine();

        $this->info('Dispatching transited-country calculation for existing transport posts...');
        $bar = $this->output->createProgressBar(TransportPost::whereNull('transited_country_codes')->count());
        TransportPost::whereNull('transited_country_codes')->chunkById($chunkSize, function ($transportPosts) use ($bar) {
            foreach ($transportPosts as $transportPost) {
                CalculateStatsForTransportPost::dispatch($transportPost->post_id);
                $bar->advance();
            }
        });
        $bar->finish();
        $this->newLine();

        $this->info('Done. Run `php artisan app:calculate-user-statistics` once the queue has drained to refresh visited_countries_count.');

        return CommandAlias::SUCCESS;
    }
}
