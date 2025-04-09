<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_locations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->decimal('latitude', 15, 8);
            $table->decimal('longitude', 15, 8);
            $table->timestamp('last_requested_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_locations');
    }
};
