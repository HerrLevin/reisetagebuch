<?php

namespace App\Console\Commands;

use App\Models\TimezoneBoundary;
use Clickbar\Magellan\IO\Parser\Geojson\GeojsonParser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ImportTimezones extends Command
{
    protected $signature = 'app:import-timezones {--path=database/data/timezones.geojson}';

    protected $description = 'Import timezone boundary polygons (timezone-boundary-builder, with oceans) into the timezone_boundaries table';

    public function handle(GeojsonParser $parser): int
    {
        $path = base_path($this->option('path'));
        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return CommandAlias::FAILURE;
        }

        $geoJson = json_decode(file_get_contents($path), true);

        foreach ($geoJson['features'] as $feature) {
            $tzid = $feature['properties']['tzid'];
            $polygons = $feature['geometry']['type'] === 'MultiPolygon'
                ? $feature['geometry']['coordinates']
                : [$feature['geometry']['coordinates']];

            $geometry = $parser->parseWithSrid(
                json_encode([
                    'type' => 'MultiPolygon',
                    'coordinates' => $polygons,
                ]),
                4326
            );

            TimezoneBoundary::updateOrCreate(['tzid' => $tzid], ['geometry' => $geometry]);
        }

        // The simplified, vendored boundaries can contain minor self-intersections
        // introduced by simplification; repair them so ST_Covers lookups don't choke.
        // ST_MakeValid can turn a sliver into a point/line, so re-extract just the
        // polygonal part to keep the column's MultiPolygon type.
        DB::statement('
            UPDATE timezone_boundaries
            SET geometry = ST_Multi(ST_CollectionExtract(ST_MakeValid(geometry), 3))
            WHERE NOT ST_IsValid(geometry)
        ');

        $this->info('Imported '.count($geoJson['features']).' timezones.');

        return CommandAlias::SUCCESS;
    }
}
