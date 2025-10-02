<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_trips', function (Blueprint $table) {
            $table->string('route_long_name')->nullable();
            $table->string('trip_short_name')->nullable();
            $table->string('display_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('transport_trips', function (Blueprint $table) {
            $table->dropColumn('route_long_name');
            $table->dropColumn('trip_short_name');
            $table->dropColumn('display_name');
        });
    }
};
