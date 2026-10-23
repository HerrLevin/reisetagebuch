<?php

use App\Models\License;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_trips', function (Blueprint $table) {
            $table->string('motis_source')->nullable()->after('provider')->comment('Raw GTFS feed source string returned by Motis for this trip');
            $table->foreignIdFor(License::class)->nullable()->after('motis_source')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transport_trips', function (Blueprint $table) {
            $table->dropConstrainedForeignIdFor(License::class);
            $table->dropColumn('motis_source');
        });
    }
};
