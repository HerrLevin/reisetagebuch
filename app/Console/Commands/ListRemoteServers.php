<?php

namespace App\Console\Commands;

use App\Models\ActivityPubServer;
use Illuminate\Console\Command;

class ListRemoteServers extends Command
{
    protected $signature = 'ap:remote-servers';

    protected $description = 'Show how many remote ActivityPub servers have interacted with this instance';

    public function handle(): int
    {
        // Filled whenever a remote server sends us a verified signed request (inbox delivery or object fetch).
        $servers = ActivityPubServer::orderByDesc('last_seen_at')->get();

        $this->table(
            ['Server', 'First seen', 'Last seen'],
            $servers->map(fn (ActivityPubServer $s) => [$s->host, $s->first_seen_at->toDateTimeString(), $s->last_seen_at->toDateTimeString()]),
        );
        $this->info("Remote servers: {$servers->count()}");

        return Command::SUCCESS;
    }
}
