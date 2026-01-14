<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('route_segments', function (Blueprint $table) {
            $table->index(['from_location_id', 'to_location_id']);
        });
    }

    public function down(): void
    {
        Schema::table('request_locations', function (Blueprint $table) {
            $table->dropIndex('route_segments_from_location_id_to_location_id_index');
        });
    }
};
