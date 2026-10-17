<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_trip_stops', function (Blueprint $table) {
            $table->string('track')->nullable()->after('departure_time');
            $table->string('scheduled_track')->nullable()->after('track');
        });
    }

    public function down(): void
    {
        Schema::table('stopovers', function (Blueprint $table) {
            $table->dropColumn('track');
            $table->dropColumn('scheduled_track');
        });
    }
};
