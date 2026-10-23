<?php

namespace App\Console\Commands;

use App\Models\License;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Console\Command\Command as CommandAlias;

class FetchTransitousLicenseOverrides extends Command
{
    private const string LICENSE_URL = 'https://raw.githubusercontent.com/Traewelling/transitous-licenses/refs/heads/main/licenses.json';

    protected $signature = 'app:fetch-transitous-license-overrides';

    protected $description = 'Apply manually curated license corrections from the Träwelling transitous-licenses repository, for feeds Transitous itself does not license correctly';

    /**
     * @throws ConnectionException
     */
    public function handle(): int
    {
        $response = Http::get(self::LICENSE_URL);
        if (! $response->successful()) {
            $this->error('Failed to download license overrides: HTTP '.$response->status());

            return CommandAlias::FAILURE;
        }

        $data = $response->json() ?? [];
        $proprietaryLicenses = collect($data['proprietary_licenses'] ?? [])->keyBy('identifier');

        foreach ($data['sources'] ?? [] as $source) {
            $file = $source['file'] ?? null;
            if (empty($file)) {
                continue;
            }

            // The daily automatic sync (app:fetch-transitous-licenses) creates one row per
            // feed Transitous reports; this only corrects/annotates rows that already exist.
            $license = License::where(['provider' => 'transitous', 'name' => $file])->first();
            if (! $license) {
                $this->warn("No synced license found for override source: {$file}");

                continue;
            }

            if (! empty($source['custom_license'])) {
                $proprietary = $proprietaryLicenses->get($source['custom_license']);
                if (! $proprietary) {
                    $this->warn("Unknown proprietary license '{$source['custom_license']}' for {$file}");

                    continue;
                }

                $license->update([
                    'human_name' => $proprietary['name'] ?? $source['custom_license'],
                    'license_url' => $proprietary['url'] ?? $source['url'] ?? $license->license_url,
                    'attribution_text' => $proprietary['attribution_text'] ?? null,
                    'active' => true,
                ]);
            } elseif (! empty($source['spdx'])) {
                $license->update([
                    'spdx' => $source['spdx'],
                    'active' => array_key_exists($source['spdx'], License::SPDX),
                ]);
            }
        }

        $this->info('Applied manual Transitous license overrides.');

        return CommandAlias::SUCCESS;
    }
}
