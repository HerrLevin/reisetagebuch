<?php

namespace App\Services\Osm;

use App\Dto\OsmImportResult;
use App\Dto\OverpassLocation;
use App\Repositories\LocationRepository;
use Closure;
use Generator;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use RuntimeException;

/**
 * Bulk-loads Locations from Geofabrik (or any) .osm.pbf extracts, instead of
 * waiting on live Overpass radius queries (see OverpassRadiusRequestService).
 * Overpass is left in place as a fallback for anything outside the imported
 * extracts.
 *
 * Requires the `osmium` CLI (osmium-tool) to be installed on PATH.
 */
readonly class OsmGeofabrikImportService
{
    // osmium decompresses PBF blocks on a thread pool sized to the CPU count by
    // default, each thread buffering its own decompressed block; capping it to 1
    // trades speed for a much smaller peak memory footprint on constrained hosts.
    private array $osmiumPoolThreads;

    public function __construct(private LocationRepository $locationRepository)
    {

        $this->osmiumPoolThreads = ['OSMIUM_POOL_THREADS' => config('app.osm.osmium_pool_threads')];
    }

    /**
     * @param  string  $extract  a local .osm.pbf file path or a download URL (e.g. a Geofabrik link)
     *
     * @throws RuntimeException if downloading, or the osmium pipeline, fails
     */
    public function import(string $extract): OsmImportResult
    {
        $report = function (string $message) use ($extract) {
            Log::info("OsmGeofabrikImportService: {$message}", ['extract' => $extract]);
        };

        // A `finally` block can't run if the process is hard-killed mid-import
        // (e.g. OOM) — that leaves its working directory (which can be several
        // GB) behind. The osm-import queue only ever runs one import at a time,
        // so anything already here on the next run must be exactly that kind of
        // orphaned debris; sweep it before starting.
        $baseDir = storage_path('app/osm-import');
        File::ensureDirectoryExists($baseDir);
        foreach (File::directories($baseDir) as $staleDir) {
            File::deleteDirectory($staleDir);
        }

        $workDir = $baseDir.'/'.uniqid();
        File::ensureDirectoryExists($workDir);

        try {
            $report("Importing {$extract}...");

            $pbfPath = $this->resolveLocalPath($extract, $workDir, $report);
            $filteredPath = $this->filterTags($pbfPath, $workDir, $report);
            $ndjsonPath = $this->exportToNdjson($filteredPath, $workDir, $report);

            $report('Reading exported features and upserting Locations...');
            $imported = 0;
            $skipped = 0;
            foreach ($this->readFeatures($ndjsonPath) as $feature) {
                $location = $this->toOverpassLocation($feature);
                if ($location === null) {
                    $skipped++;

                    continue;
                }

                $imported++;
                $this->locationRepository->updateOrCreateOsmLocation($location, true);
                if ($imported % 5000 === 0) {
                    $report("...{$imported} locations imported so far");
                }
            }

            $report("Done: {$imported} locations imported, {$skipped} elements skipped (filtered out or unusable).");

            return new OsmImportResult($extract, $imported, $skipped);
        } finally {
            File::deleteDirectory($workDir);
        }
    }

    /**
     * @param  Closure(string): void  $report
     *
     * @throws ConnectionException
     */
    private function resolveLocalPath(string $extract, string $workDir, Closure $report): string
    {
        if (! str_starts_with($extract, 'http://') && ! str_starts_with($extract, 'https://')) {
            if (! is_file($extract)) {
                throw new RuntimeException("File not found: {$extract}");
            }

            return $extract;
        }

        $report("Downloading {$extract}...");

        // Geofabrik serves "*-latest.osm.pbf" as a redirect to the actual dated
        // filename. Resolve that ourselves first and download the final URL
        // directly — Guzzle's redirect handling and the "sink" option used
        // below don't combine well (the sink can end up with a redirect
        // response's body instead of the real file).
        $finalUrl = $this->resolveRedirects($extract, $report);
        $localPath = $workDir.'/'.basename(parse_url($finalUrl, PHP_URL_PATH) ?: 'download.osm.pbf');

        // Guzzle's "progress" option fires on every chunk, not just every 10%,
        // so only report once we cross into the next 10%-bucket.
        $lastReportedBucket = -1;
        $response = Http::sink($localPath)->withOptions([
            'allow_redirects' => false,
            'progress' => function (int $downloadTotal, int $downloadedBytes) use ($report, &$lastReportedBucket) {
                if ($downloadTotal <= 0) {
                    return;
                }

                $bucket = intdiv($downloadedBytes * 100, $downloadTotal * 10) * 10;
                if ($bucket > $lastReportedBucket) {
                    $lastReportedBucket = $bucket;
                    $report("Download progress: {$bucket}% ({$downloadedBytes}/{$downloadTotal} bytes)");
                }
            },
        ])->timeout(0)->get($finalUrl);

        if (! $response->successful()) {
            throw new RuntimeException("Download failed for {$finalUrl}: HTTP {$response->status()}");
        }

        $report("Download complete: {$localPath} (".filesize($localPath).' bytes)');

        return $localPath;
    }

    /**
     * @param  Closure(string): void  $report
     *
     * @throws ConnectionException
     */
    private function resolveRedirects(string $url, Closure $report, int $maxHops = 5): string
    {
        for ($hop = 0; $hop < $maxHops; $hop++) {
            $response = Http::withOptions(['allow_redirects' => false])->timeout(0)->head($url);
            if (! $response->redirect()) {
                return $url;
            }

            $location = $response->header('Location');
            if (empty($location)) {
                return $url;
            }

            $report("Following redirect: {$url} -> {$location}");
            $url = $location;
        }

        throw new RuntimeException("Too many redirects resolving {$url}");
    }

    /**
     * @param  Closure(string): void  $report
     *
     * @throws ConnectionException
     */
    private function filterTags(string $pbfPath, string $workDir, Closure $report): string
    {
        $report('Running osmium tags-filter...');
        $filteredPath = $workDir.'/filtered.osm.pbf';
        $result = Process::timeout(0)->env($this->osmiumPoolThreads)->run([
            'osmium', 'tags-filter', $pbfPath, ...OsmTagFilter::allKeys(),
            '-o', $filteredPath, '--overwrite',
        ]);
        if ($result->failed()) {
            throw new RuntimeException("osmium tags-filter failed for {$pbfPath}: ".$result->errorOutput());
        }

        $report("tags-filter complete: {$filteredPath} (".filesize($filteredPath).' bytes)');

        return $filteredPath;
    }

    /**
     * @param  Closure(string): void  $report
     */
    private function exportToNdjson(string $filteredPath, string $workDir, Closure $report): string
    {
        $configPath = $workDir.'/export-config.json';
        file_put_contents($configPath, json_encode(['attributes' => ['id' => true, 'type' => true]]));

        $report('Running osmium export...');
        $ndjsonPath = $workDir.'/export.ndjson';
        $result = Process::timeout(0)->env($this->osmiumPoolThreads)->run([
            'osmium', 'export', $filteredPath, '-f', 'jsonseq',
            // osmium's default location index sizes itself off OSM's global node-ID
            // range, not this extract's actual size, and can get OOM-killed even on
            // a regional extract; sparse_mem_array scales with the extract instead.
            '--index-type=sparse_mem_array',
            '-o', $ndjsonPath, '--overwrite', '-c', $configPath,
        ]);
        if ($result->failed()) {
            throw new RuntimeException("osmium export failed for {$filteredPath}: ".$result->errorOutput());
        }

        $report("export complete: {$ndjsonPath} (".filesize($ndjsonPath).' bytes)');

        return $ndjsonPath;
    }

    /**
     * @return Generator<array>
     */
    private function readFeatures(string $ndjsonPath): Generator
    {
        $handle = fopen($ndjsonPath, 'r');
        if ($handle === false) {
            return;
        }

        while (($line = fgets($handle)) !== false) {
            // osmium's jsonseq output follows RFC 8142 and prefixes each record
            // with an ASCII Record Separator (0x1E); strip it before decoding.
            $line = trim($line, "\x1E\r\n");
            if ($line === '') {
                continue;
            }

            $feature = json_decode($line, true);
            if (is_array($feature)) {
                yield $feature;
            }
        }

        fclose($handle);
    }

    private function toOverpassLocation(array $feature): ?OverpassLocation
    {
        $props = $feature['properties'] ?? [];
        $tags = array_diff_key($props, array_flip(['@id', '@type']));

        if (! OsmTagFilter::matches($tags)) {
            return null;
        }

        $osmId = $props['@id'] ?? null;
        $osmType = $props['@type'] ?? null;
        $coordinates = $this->approximateCenter($feature['geometry'] ?? null);

        if ($osmId === null || $osmType === null || $coordinates === null) {
            Log::warning('OsmGeofabrikImportService: skipped feature with missing id/type/geometry', ['properties' => $props]);

            return null;
        }

        [$longitude, $latitude] = $coordinates;

        return new OverpassLocation(
            osmId: (string) $osmId,
            latitude: $latitude,
            longitude: $longitude,
            osmType: $osmType,
            tags: $tags,
        );
    }

    /**
     * shortcut: approximates a way/relation's center as the plain average of
     * its vertices (not an area-weighted centroid) — fine for "nearby POI"
     * purposes; upgrade to a proper centroid if precise placement matters.
     *
     * @return array{0: float, 1: float}|null [longitude, latitude]
     */
    private function approximateCenter(?array $geometry): ?array
    {
        if ($geometry === null) {
            return null;
        }

        $points = match ($geometry['type'] ?? null) {
            'Point' => [$geometry['coordinates']],
            'LineString' => $geometry['coordinates'],
            'Polygon' => $geometry['coordinates'][0] ?? [],
            'MultiPolygon' => array_merge(...array_map(fn ($polygon) => $polygon[0] ?? [], $geometry['coordinates'])),
            default => [],
        };

        if (empty($points)) {
            return null;
        }

        $longitude = array_sum(array_column($points, 0)) / count($points);
        $latitude = array_sum(array_column($points, 1)) / count($points);

        return [$longitude, $latitude];
    }
}
