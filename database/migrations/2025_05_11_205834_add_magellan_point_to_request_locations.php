<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('request_locations', function (Blueprint $table) {
            $table->magellanPoint('location')->after('id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('request_locations', function (Blueprint $table) {
            $table->dropColumn('location');
        });
    }
};
