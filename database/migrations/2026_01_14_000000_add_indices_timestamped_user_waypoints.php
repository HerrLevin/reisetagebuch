<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timestamped_user_waypoints', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('timestamped_user_waypoints', function (Blueprint $table) {
            $table->dropIndex('timestamped_user_waypoints_user_id_created_at_index');
        });
    }
};
