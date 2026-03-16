<?php

use App\Jobs\CalculateStatsForTransportPost;
use App\Models\RouteSegment;
use App\Models\TransportPost;
use App\Repositories\TransportTripRepository;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        RouteSegment::chunkById(100, function ($segments) {
            foreach ($segments as $segment) {
                $repo = new TransportTripRepository;
                $repo->calculateDistance($segment);
                $segment->save();
            }
        });

        TransportPost::chunkById(100, function ($posts) {
            /** @var TransportPost $post */
            foreach ($posts as $post) {
                CalculateStatsForTransportPost::dispatch($post->post_id);
            }
        });
    }
};
