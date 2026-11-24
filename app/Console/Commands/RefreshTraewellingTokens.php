<?php

namespace App\Console\Commands;

use App\Models\SocialAccount;
use App\Services\TraewellingRequestService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class RefreshTraewellingTokens extends Command
{
    protected $signature = 'app:refresh-traewelling-tokens {--stale-days=7 : Days since the last refresh before tokens are refreshed again}';

    protected $description = 'Refresh Träwelling OAuth tokens regularly so refresh tokens never expire';

    public function handle(TraewellingRequestService $traewellingRequestService): int
    {
        // updated_at only changes when tokens are written, so it marks the last refresh
        $accounts = SocialAccount::whereProvider('traewelling')
            ->whereNotNull('refresh_token')
            ->where('updated_at', '<', now()->subDays((int) $this->option('stale-days')))
            ->get();

        $failed = 0;
        foreach ($accounts as $account) {
            try {
                $traewellingRequestService->refreshToken($account);
            } catch (Throwable $e) {
                $failed++;
                Log::warning('RefreshTraewellingTokens: failed to refresh token', ['userId' => $account->user_id, 'error' => $e->getMessage()]);
            }
        }

        $this->info("Refreshed {$accounts->count()} Träwelling accounts, {$failed} failed.");

        return Command::SUCCESS;
    }
}
