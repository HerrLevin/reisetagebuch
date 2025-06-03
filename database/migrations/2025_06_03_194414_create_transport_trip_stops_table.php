<?php

use App\Models\Location;
use App\Models\RouteSegment;
use App\Models\TransportTrip;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_trip_stops', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(TransportTrip::class)->constrained();
            $table->foreignIdFor(Location::class)->constrained();
            $table->timestampTz('arrival_time')
                ->comment('Arrival time at the stop, in UTC')->nullable();
            $table->timestampTz('departure_time')
                ->comment('Departure time from the stop, in UTC')->nullable();
            $table->integer('arrival_delay')
                ->nullable()
                ->comment('Delay in seconds at arrival, 0 if on time, null if not applicable');
            $table->integer('departure_delay')
                ->nullable()
                ->comment('Delay in seconds at departure, 0 if on time, null if not applicable');
            $table->integer('stop_sequence')
                ->comment('Sequence number of the stop in the trip, starting from 0');
            $table->boolean('cancelled')
                ->default(false)
                ->comment('Indicates if the stop was cancelled, true if cancelled, false otherwise');
            $table->foreignIdFor(RouteSegment::class)->nullable()->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transport_trip_stops');
    }
};
