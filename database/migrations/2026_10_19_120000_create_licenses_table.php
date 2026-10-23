<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('provider')->comment('Data provider the license applies to, e.g. "transitous"');
            $table->string('country')->comment('Country/region prefix of the GTFS feed, e.g. "de"');
            $table->string('name')->comment('GTFS feed filename this license applies to, e.g. "de_db.gtfs.zip"');
            $table->string('human_name')->nullable()->comment('Human-readable name of the data publisher');
            $table->string('spdx')->nullable()->comment('SPDX license identifier, e.g. "CC-BY-4.0"');
            $table->string('license_url')->nullable();
            $table->string('source_url')->nullable();
            $table->text('attribution_text')->nullable();
            $table->boolean('active')->default(false)->comment('Whether the license is known and safe to display attribution for');
            $table->timestamps();

            $table->unique(['provider', 'country', 'name'], 'licenses_provider_country_name_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
