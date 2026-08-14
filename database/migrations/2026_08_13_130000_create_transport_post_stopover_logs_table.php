<?php

use App\Models\TransportPost;
use App\Models\TransportTripStop;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_post_stopover_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(TransportPost::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(TransportTripStop::class)->constrained()->cascadeOnDelete();
            $table->timestampTz('manual_arrival')
                ->comment('User-logged actual arrival time at the stop, in UTC')->nullable();
            $table->timestampTz('manual_departure')
                ->comment('User-logged actual departure time from the stop, in UTC')->nullable();
            $table->timestamps();

            $table->unique(['transport_post_id', 'transport_trip_stop_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_post_stopover_logs');
    }
};
