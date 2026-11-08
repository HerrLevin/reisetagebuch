<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_pub_servers', function (Blueprint $table) {
            $table->string('host')->primary();
            $table->timestamp('first_seen_at');
            $table->timestamp('last_seen_at');
        });

        // Backfill from actors we already know.
        $rows = DB::table('activity_pub_actors')->get(['actor_uri', 'created_at', 'updated_at'])
            ->groupBy(fn ($actor) => strtolower((string) parse_url($actor->actor_uri, PHP_URL_HOST)))
            ->filter(fn ($actors, $host) => $host !== '')
            ->map(fn ($actors, $host) => [
                'host' => $host,
                'first_seen_at' => $actors->min('created_at') ?? now(),
                'last_seen_at' => $actors->max('updated_at') ?? now(),
            ])
            ->values();

        foreach ($rows->chunk(500) as $chunk) {
            DB::table('activity_pub_servers')->insert($chunk->all());
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_pub_servers');
    }
};
