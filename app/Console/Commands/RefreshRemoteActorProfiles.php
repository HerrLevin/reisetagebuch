<?php

namespace App\Console\Commands;

use App\Models\ActivityPubActor;
use App\Services\ActivityPubService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RefreshRemoteActorProfiles extends Command
{
    protected $signature = 'ap:refresh-remote-actors {--stale-days=7 : Days before a profile is considered stale} {--limit=100 : Maximum number of actors to refresh}';

    protected $description = 'Refresh cached remote ActivityPub actor profiles and avatars';

    public function handle(ActivityPubService $activityPubService): int
    {
        $staleDays = (int) $this->option('stale-days');
        $limit = (int) $this->option('limit');

        $actors = ActivityPubActor::where(function ($query) use ($staleDays) {
            $query->whereNull('profile_fetched_at')
                ->orWhere('profile_fetched_at', '<', now()->subDays($staleDays));
        })
            ->orderBy('profile_fetched_at')
            ->limit($limit)
            ->get();

        $this->info("Refreshing {$actors->count()} stale actor profiles...");

        $refreshed = 0;
        $failed = 0;

        foreach ($actors as $actor) {
            try {
                $activityPubService->resolveActor($actor->actor_uri);
                $refreshed++;
            } catch (\Exception $e) {
                $failed++;
                Log::warning('RefreshRemoteActorProfiles: Failed to refresh actor', [
                    'actorUri' => $actor->actor_uri,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Done. Refreshed: {$refreshed}, Failed: {$failed}");

        return Command::SUCCESS;
    }
}
