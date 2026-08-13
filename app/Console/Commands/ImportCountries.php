<?php

namespace App\Console\Commands;

use App\Dto\Coordinate;
use App\Models\Country;
use App\Services\GeoService;
use Clickbar\Magellan\Data\Geometries\MultiPolygon;
use Clickbar\Magellan\IO\Parser\Geojson\GeojsonParser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ImportCountries extends Command
{
    protected $signature = 'app:import-countries {--path=database/data/ne_50m_admin_0_countries.geojson}';

    protected $description = 'Import country boundary polygons (Natural Earth admin-0) into the countries table';

    /**
     * Natural Earth leaves ISO_A2/ISO_A2_EH empty (-99) for a handful of disputed or
     * non-ISO territories. Assign stable, non-conflicting placeholder codes for the
     * ones plausible as travel destinations; everything else is skipped.
     */
    private const array ISO_A2_OVERRIDES = [
        'KOS' => 'XK', // Kosovo (also fixable via ISO_A2_EH, listed for clarity)
        'SOL' => 'XS', // Somaliland (no official ISO code; avoid clashing with Somalia = SO)
        'CYN' => 'XN', // Northern Cyprus (no official ISO code; avoid clashing with Cyprus = CY)
    ];

    /**
     * Natural Earth models these countries as a single multipart polygon that also
     * includes far-flung overseas departments/territories on other continents (e.g.
     * France's feature includes French Guiana, Réunion, Martinique, Guadeloupe, Mayotte).
     * For a travel map, someone visiting metropolitan France shouldn't also light up
     * French Guiana, so only the components reasonably close to the main landmass are
     * kept. Large-but-legitimately-scattered countries (USA, Russia, Indonesia, Japan,
     * Australia, ...) are deliberately not in this list, since their outlying components
     * are part of the same national territory, not colonial-era overseas departments.
     */
    private const array MAIN_LANDMASS_ONLY = ['FRA', 'NLD'];

    private const int MAIN_LANDMASS_RADIUS_METERS = 1_200_000;

    public function handle(GeojsonParser $parser, GeoService $geoService): int
    {
        $path = base_path($this->option('path'));
        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return CommandAlias::FAILURE;
        }

        $geoJson = json_decode(file_get_contents($path), true);

        /** @var array<string, array{name: string, polygons: array}> $grouped */
        $grouped = [];

        foreach ($geoJson['features'] as $feature) {
            $props = $feature['properties'];
            $isoA2 = $this->resolveIsoA2($props);

            if ($isoA2 === null) {
                $this->warn("Skipping {$props['NAME']}: no resolvable ISO A2 code");
                Log::warning('ImportCountries: skipped feature with no resolvable ISO A2 code', ['name' => $props['NAME']]);

                continue;
            }

            $polygons = $feature['geometry']['type'] === 'MultiPolygon'
                ? $feature['geometry']['coordinates']
                : [$feature['geometry']['coordinates']];

            if (in_array($props['ADM0_A3'] ?? null, self::MAIN_LANDMASS_ONLY, true)) {
                $polygons = $this->filterToMainLandmass($polygons, $geoService);
            }

            if (! isset($grouped[$isoA2])) {
                $grouped[$isoA2] = ['name' => $props['NAME'], 'polygons' => []];
            }
            array_push($grouped[$isoA2]['polygons'], ...$polygons);
        }

        foreach ($grouped as $isoA2 => $data) {
            /** @var MultiPolygon $geometry */
            $geometry = $parser->parseWithSrid(
                json_encode([
                    'type' => 'MultiPolygon',
                    'coordinates' => $data['polygons'],
                ]),
                4326
            );

            Country::updateOrCreate(
                ['iso_a2' => $isoA2],
                ['name' => $data['name'], 'geometry' => $geometry]
            );
        }

        $this->info('Imported '.count($grouped).' countries.');

        return CommandAlias::SUCCESS;
    }

    /**
     * @param  array<int, array>  $polygons  a list of GeoJSON Polygon "coordinates" arrays
     * @return array<int, array>
     */
    private function filterToMainLandmass(array $polygons, GeoService $geoService): array
    {
        $components = array_map(function (array $polygon) {
            [$area, $centroid] = $this->polygonAreaAndCentroid($polygon[0]);

            return ['polygon' => $polygon, 'area' => $area, 'centroid' => $centroid];
        }, $polygons);

        usort($components, fn ($a, $b) => $b['area'] <=> $a['area']);
        $mainCentroid = $this->toCoordinate($components[0]['centroid']);

        return array_values(array_map(
            fn ($component) => $component['polygon'],
            array_filter(
                $components,
                fn ($component) => $geoService->getDistance($mainCentroid, $this->toCoordinate($component['centroid'])) <= self::MAIN_LANDMASS_RADIUS_METERS
            )
        ));
    }

    /**
     * @param  array{0: float, 1: float}  $lonLat
     */
    private function toCoordinate(array $lonLat): Coordinate
    {
        return new Coordinate($lonLat[1], $lonLat[0]);
    }

    /**
     * Planar shoelace area/centroid on lon/lat coordinates. Not geodetically accurate,
     * but only used to rank polygon components by relative size and rough position.
     *
     * @param  array<int, array{0: float, 1: float}>  $ring
     * @return array{0: float, 1: array{0: float, 1: float}}
     */
    private function polygonAreaAndCentroid(array $ring): array
    {
        $area = 0.0;
        $cx = 0.0;
        $cy = 0.0;
        $pointCount = count($ring);

        for ($i = 0; $i < $pointCount - 1; $i++) {
            [$x1, $y1] = $ring[$i];
            [$x2, $y2] = $ring[$i + 1];
            $cross = $x1 * $y2 - $x2 * $y1;
            $area += $cross;
            $cx += ($x1 + $x2) * $cross;
            $cy += ($y1 + $y2) * $cross;
        }

        $area /= 2;
        if (abs($area) < 1e-9) {
            $lons = array_column($ring, 0);
            $lats = array_column($ring, 1);

            return [1e-9, [array_sum($lons) / count($lons), array_sum($lats) / count($lats)]];
        }

        return [abs($area), [$cx / (6 * $area), $cy / (6 * $area)]];
    }

    private function resolveIsoA2(array $props): ?string
    {
        foreach ([$props['ISO_A2'] ?? null, $props['ISO_A2_EH'] ?? null] as $candidate) {
            // Natural Earth uses non-ISO placeholders like "CN-TW" (Taiwan) or "-99" for
            // codes it can't/won't assign a plain ISO A2 for; only a real 2-letter code counts.
            if (is_string($candidate) && strlen($candidate) === 2) {
                return $candidate;
            }
        }

        return self::ISO_A2_OVERRIDES[$props['ADM0_A3'] ?? ''] ?? null;
    }
}
