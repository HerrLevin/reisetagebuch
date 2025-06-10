<?php

use App\Models\Location;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_segments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Location::class, 'from_location_id')->constrained();
            $table->foreignIdFor(Location::class, 'to_location_id')->constrained();
            $table->integer('distance')->comment('Distance in meters');
            $table->integer('duration')->comment('Duration in seconds')->nullable();
            $table->string('path_type')->comment('Type of path, e.g., rail, road, trail')->nullable();
            $table->magellanLineStringZ('geometry')
                ->comment('Geospatial data representing the route segment');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_segments');
    }
};
