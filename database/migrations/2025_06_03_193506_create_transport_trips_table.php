<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_trips', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('foreign_trip_id')->comment('Unique identifier for the trip in the external system')->nullable();
            $table->string('provider')->comment('Name of the data provider, e.g., "TransportAPI"')->nullable();
            $table->string('mode')->comment('Transport mode, e.g., "bus", "train", "car"');
            $table->string('line_name')->comment('Name of the transport line, e.g., "Line 1", "Route A"')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'foreign_trip_id'], 'unique_provider_mode_foreign_trip');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_trips');
    }
};
