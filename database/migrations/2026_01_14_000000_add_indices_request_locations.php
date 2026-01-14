<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_locations', function (Blueprint $table) {
            $table->index(['location', 'last_requested_at', 'radius']);
        });
    }

    public function down(): void
    {
        Schema::table('request_locations', function (Blueprint $table) {
            $table->dropIndex('request_locations_location_last_requested_at_radius_index');
        });
    }
};
