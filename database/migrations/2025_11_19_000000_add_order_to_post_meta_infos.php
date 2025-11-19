<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_meta_infos', function (Blueprint $table) {
            $table->dropUnique('post_meta_infos_unique_key');
            $table->unsignedSmallInteger('order')->nullable();
            $table->unique(['post_id', 'key', 'order'], 'post_meta_infos_unique_key');
        });
    }

    public function down(): void
    {
        Schema::table('post_meta_infos', function (Blueprint $table) {
            $table->dropUnique('post_meta_infos_unique_key');
            $table->dropColumn('order');
            $table->unique(['post_id', 'key'], 'post_meta_infos_unique_key');
        });
    }
};
