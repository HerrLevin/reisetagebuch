<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_posts', function (Blueprint $table) {
            $table->unsignedInteger('distance')->default(0)->comment('distance in kilometers');
        });
    }

    public function down(): void
    {
        Schema::table('transport_posts', function (Blueprint $table) {
            $table->dropColumn('distance');
        });
    }
};
