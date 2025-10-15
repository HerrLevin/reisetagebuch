<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_posts', function (Blueprint $table) {
            $table->timestampTz('manual_departure')->nullable();
            $table->timestampTz('manual_arrival')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('transport_posts', function (Blueprint $table) {
            $table->dropColumn('manual_departure');
            $table->dropColumn('manual_arrival');
        });
    }
};
