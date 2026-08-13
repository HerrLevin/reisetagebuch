<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('iso_a2', 2)->unique();
            $table->string('name');
            $table->magellanMultiPolygon('geometry');
            $table->timestamps();
        });

        DB::statement('CREATE INDEX countries_geometry_gist ON countries USING GIST (geometry)');
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
