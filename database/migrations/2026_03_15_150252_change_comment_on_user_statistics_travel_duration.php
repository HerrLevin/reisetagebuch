<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_statistics', function (Blueprint $table) {
            $table->unsignedInteger('travelled_duration')->default(0)->comment('in seconds')->change();
        });
    }

    public function down(): void
    {
        Schema::table('user_statistics', function (Blueprint $table) {
            $table->unsignedInteger('travelled_duration')->default(0)->comment('in minutes')->change();
        });
    }
};
