<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts_hash_tags_maps', function (Blueprint $table) {
            $table->index('hash_tag_id');
            $table->index('post_id');
        });
    }

    public function down(): void
    {
        Schema::table('posts_hash_tags_maps', function (Blueprint $table) {
            $table->dropIndex('posts_hash_tags_maps_hash_tag_id_index');
            $table->dropIndex('posts_hash_tags_maps_post_id_index');
        });
    }
};
