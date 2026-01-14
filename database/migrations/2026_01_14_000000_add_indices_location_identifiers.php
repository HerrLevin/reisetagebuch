<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('location_identifiers', function (Blueprint $table) {
            $table->index('location_id');
            $table->index(['type', 'origin', 'identifier']);
        });
    }

    public function down(): void
    {
        Schema::table('location_identifiers', function (Blueprint $table) {
            $table->dropIndex('location_identifiers_location_id_index');
            $table->dropIndex('location_identifiers_type_origin_identifier_index');
        });
    }
};
