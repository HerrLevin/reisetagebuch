<?php

use App\Jobs\RerouteStops;
use App\Models\TransportTrip;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // batch get all transport trips + dispatch RerouteStops Job
        TransportTrip::with('stops')->chunk(100, function ($trips) {
            foreach ($trips as $trip) {
                $stops = [];
                foreach ($trip->stops as $stop) {
                    $stops[] = $stop;
                }
                RerouteStops::dispatch($trip, $stops);
            }
        });
    }

    public function down(): void
    {
        // nothing to do
    }
};
