<?php

use App\Models\TransportPost;
use App\Models\TransportTrip;
use App\Models\TransportTripStop;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // iterate over all transport posts
        // fetch 100 at a time
        TransportPost::chunk(100, function ($posts) {
            /** @var TransportPost $post */
            foreach ($posts as $post) {
                $trip = TransportTrip::create([
                    'mode' => $post->mode,
                    'line_name' => $post->line,
                ]);

                $origin = TransportTripStop::create([
                    'transport_trip_id' => $trip->id,
                    'location_id' => $post->origin_id,
                    'departure_time' => $post->departure,
                    'departure_delay' => $post->departure_delay,
                    'stop_sequence' => 0,
                ]);
                $destination = TransportTripStop::create([
                    'transport_trip_id' => $trip->id,
                    'location_id' => $post->destination_id,
                    'arrival_time' => $post->arrival,
                    'arrival_delay' => $post->arrival_delay,
                    'stop_sequence' => 1,
                ]);

                $post->transport_trip_id = $trip->id;
                $post->origin_stop_id = $origin->id;
                $post->destination_stop_id = $destination->id;
                $post->save();
            }
        });
    }

    public function down(): void
    {
        // This migration is irreversible as it converts transport posts to use trips.
        // If you need to revert, you would have to manually restore the previous state.
        // This is a one-way migration.
    }
};
