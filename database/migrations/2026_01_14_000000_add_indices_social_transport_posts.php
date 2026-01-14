<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_posts', function (Blueprint $table) {
            $table->index('post_id');
            $table->index('origin_stop_id');
            $table->index('destination_stop_id');
            $table->index('transport_trip_id');
        });
    }

    public function down(): void
    {
        Schema::table('transport_posts', function (Blueprint $table) {
            $table->dropIndex('transport_posts_post_id_index');
            $table->dropIndex('transport_posts_origin_stop_id_index');
            $table->dropIndex('transport_posts_destination_stop_id_index');
            $table->dropIndex('transport_posts_transport_trip_id_index');
        });
    }
};
