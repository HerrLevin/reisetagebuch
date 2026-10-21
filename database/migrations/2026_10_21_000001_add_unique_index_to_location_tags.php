<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('location_tags', function (Blueprint $table) {
            $table->dropIndex('location_tags_location_id_index');
            $table->unique(['location_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::table('location_tags', function (Blueprint $table) {
            $table->dropUnique(['location_id', 'key']);
            $table->index('location_id');
        });
    }
};
