<?php

use App\Models\Location;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('location_identifiers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Location::class);
            $table->string('type');
            $table->string('identifier');
            $table->string('origin');
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_identifiers');
    }
};
