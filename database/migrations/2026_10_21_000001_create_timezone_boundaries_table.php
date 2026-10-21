<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timezone_boundaries', function (Blueprint $table) {
            $table->id();
            $table->string('tzid')->unique();
            $table->magellanMultiPolygon('geometry');
            $table->timestamps();
        });

        DB::statement('CREATE INDEX timezone_boundaries_geometry_gist ON timezone_boundaries USING GIST (geometry)');
    }

    public function down(): void
    {
        Schema::dropIfExists('timezone_boundaries');
    }
};
