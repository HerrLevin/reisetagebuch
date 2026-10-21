<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // A plain unique index on (type, origin, identifier) isn't safe for every
        // origin: e.g. the Träwelling cross-posting flow intentionally keys its
        // "stop" identifier by (origin, type, location_id) and can legitimately
        // assign the same external Träwelling station id to two different
        // locations. Only the OSM import path needs (type, identifier) to be
        // unique, so scope the constraint to origin = 'osm' via a partial index.
        DB::statement(
            "create unique index location_identifiers_osm_type_identifier_unique
                on location_identifiers (type, identifier) where origin = 'osm'"
        );
    }

    public function down(): void
    {
        DB::statement('drop index if exists location_identifiers_osm_type_identifier_unique');
    }
};
