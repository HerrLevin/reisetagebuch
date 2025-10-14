<?php

namespace App\Jobs;

use App\Dto\AirportDto;
use App\Repositories\LocationRepository;
use Generator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class FetchAirports implements ShouldQueue
{
    use Queueable;

    private const string AIRPORTS = 'https://davidmegginson.github.io/ourairports-data/airports.csv';

    private LocationRepository $locationRepository;

    public function __construct(?LocationRepository $locationRepository = null)
    {
        $this->locationRepository = $locationRepository ?? app(LocationRepository::class);
    }

    public function handle(): void
    {
        $count = 0;
        Log::info('Starting to fetch and store airports');

        foreach ($this->pullAirports() as $airport) {
            if ($airport === null) {
                continue;
            }
            $count++;
            $this->locationRepository->updateOrCreateAirport($airport);

            if ($count % 100 === 0) {
                Log::debug(sprintf('Stored %d airports', $count));
            }
        }

        Log::info(sprintf('Finished storing %d airports', $count));
    }

    public function pullAirports(): ?Generator
    {
        Log::info('Fetching airports...');
        $csv = file_get_contents(self::AIRPORTS);
        if ($csv === false) {
            Log::error('Failed to fetch airports');

            return null;
        }

        $lines = explode("\n", $csv);
        $header = str_getcsv(array_shift($lines));

        Log::info(sprintf('Fetched %d lines of airport data', count($lines)));

        $count = 0;
        foreach ($lines as $line) {
            yield $this->parseLine($line, $header);
            $count++;
            if ($count % 100 === 0) {
                Log::debug(sprintf('Parsed %d lines of airport data', $count));
            }
        }

        Log::debug(sprintf('Done Parsing. Parsed %d lines of airport data.', $count));

        return null;
    }

    private function parseLine($line, $header): ?AirportDto
    {
        $data = str_getcsv($line);
        if (count($data) !== count($header)) {
            return null;
        }
        $airport = array_combine($header, $data);

        if (! $airport) {
            return null;
        }
        if ($airport['type'] === 'large_airport' || $airport['type'] === 'medium_airport' || $airport['type'] === 'small_airport' || $airport['scheduled_service'] === 'yes') {
            $dto = new AirportDto;
            $dto->foreignIdentifier = $airport['id'];
            $dto->ident = $airport['ident'];
            $dto->provider = 'ourairports';
            $dto->name = $airport['name'];
            $dto->latitude = (float) $airport['latitude_deg'];
            $dto->longitude = (float) $airport['longitude_deg'];
            $dto->type = $airport['type'];
            $dto->municipality = $airport['municipality'] ?: null;
            $dto->icaoCode = $airport['icao_code'] ?: null;
            $dto->iataCode = $airport['iata_code'] ?: null;
            $dto->gpsCode = $airport['gps_code'] ?: null;
            $dto->localCode = $airport['local_code'] ?: null;

            return $dto;
        }

        return null;
    }
}
