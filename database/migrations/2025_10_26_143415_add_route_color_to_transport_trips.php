<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_trips', function (Blueprint $table) {
            $table->string('route_color')->nullable()->after('trip_short_name');
            $table->string('route_text_color')->nullable()->after('route_color');
        });
    }

    public function down(): void
    {
        Schema::table('transport_trips', function (Blueprint $table) {
            $table->dropColumn('route_color');
            $table->dropColumn('route_text_color');
        });
    }
};
