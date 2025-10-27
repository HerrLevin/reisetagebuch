<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_trips', function (Blueprint $table) {
            $table->timestamp('last_refreshed_at')->nullable()->after('scheduled_end_time');
            $table->index('last_refreshed_at');
        });
    }

    public function down(): void
    {
        Schema::table('transport_trips', function (Blueprint $table) {
            $table->dropIndex(['last_refreshed_at']);
            $table->dropColumn('last_refreshed_at');
        });
    }
};
