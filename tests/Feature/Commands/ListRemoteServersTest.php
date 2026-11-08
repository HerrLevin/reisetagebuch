<?php

namespace Tests\Feature\Commands;

use App\Models\ActivityPubServer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListRemoteServersTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_recorded_servers(): void
    {
        $this->travelTo('2026-11-01 10:00:00');
        ActivityPubServer::recordSeen('https://chaos.social/users/c');
        $this->travelTo('2026-11-02 10:00:00');
        ActivityPubServer::recordSeen('https://mastodon.social/actor');
        ActivityPubServer::recordSeen('https://Mastodon.social/users/b');

        $this->artisan('ap:remote-servers')
            ->expectsTable(['Server', 'First seen', 'Last seen'], [
                ['mastodon.social', '2026-11-02 10:00:00', '2026-11-02 10:00:00'],
                ['chaos.social', '2026-11-01 10:00:00', '2026-11-01 10:00:00'],
            ])
            ->expectsOutput('Remote servers: 2')
            ->assertSuccessful();
    }
}
