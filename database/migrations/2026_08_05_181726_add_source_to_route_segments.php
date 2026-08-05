<?php

use App\Enums\RouteSegmentSource;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('route_segments', function (Blueprint $table) {
            $table->string('source')->default(RouteSegmentSource::BROUTER->value);
        });
    }

    public function down(): void
    {
        Schema::table('route_segments', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
