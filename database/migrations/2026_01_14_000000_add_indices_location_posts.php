<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('location_posts', function (Blueprint $table) {
            $table->index('post_id');
        });
    }

    public function down(): void
    {
        Schema::table('location_posts', function (Blueprint $table) {
            $table->dropIndex('location_posts_post_id_index');
        });
    }
};
