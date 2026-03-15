<?php

use App\Jobs\CalculateStatsForTransportPost;
use App\Models\TransportPost;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        TransportPost::chunkById(100, function ($posts) {
            /** @var TransportPost $post */
            foreach ($posts as $post) {
                CalculateStatsForTransportPost::dispatch($post->post_id);
            }
        });
    }
};
