<?php

namespace App\Console\Commands;

use App\Models\License;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Console\Command\Command as CommandAlias;

class FetchTransitousLicenses extends Command
{
    private const string LICENSE_URL = 'https://api.transitous.org/gtfs/license.json';

    protected $signature = 'app:fetch-transitous-licenses';

    protected $description = 'Sync GTFS feed license data from the Transitous license repository';

    /**
     * @throws ConnectionException
     */
    public function handle(): int
    {
        $response = Http::get(self::LICENSE_URL);
        if (! $response->successful()) {
            $this->error('Failed to download licenses: HTTP '.$response->status());

            return CommandAlias::FAILURE;
        }

        foreach ($response->json() ?? [] as $entry) {
            $country = $entry['country_code'] ?? null;
            $name = $entry['filename'] ?? null;
            if (empty($country) || empty($name)) {
                continue;
            }

            $spdx = $entry['spdx_license_identifier'] ?? null;

            License::updateOrCreate(
                ['provider' => 'transitous', 'country' => strtolower($country), 'name' => $name],
                [
                    'human_name' => $entry['publisher']['name'] ?? $entry['human_name'] ?? null,
                    'spdx' => $spdx,
                    'license_url' => $entry['license_url'] ?? null,
                    'source_url' => $entry['publisher']['url'] ?? $entry['source'] ?? null,
                    'attribution_text' => $entry['attribution_text'] ?? null,
                    'active' => ($spdx !== null && array_key_exists($spdx, License::SPDX)) || ! empty($entry['attribution_text']),
                ]
            );
        }

        $this->info('Synced Transitous license data.');

        return CommandAlias::SUCCESS;
    }
}
