<?php

namespace App\Console\Commands;

use App\Jobs\CalculateStatisticsForUser;
use App\Models\User;
use Illuminate\Console\Command;

class CalculateUserStatistics extends Command
{
    protected $signature = 'app:calculate-user-statistics';

    protected $description = 'Calculate and update user statistics for all users';

    public function handle()
    {
        $count = 0;
        User::all()->each(function ($user) use (&$count) {
            $count++;
            // dispatch but delay incrementally to avoid overloading the queue
            CalculateStatisticsForUser::dispatch($user->id)->delay(now()->addSeconds(5 * $count));
        });

        $this->info('User statistics calculation completed successfully.');

        return Command::SUCCESS;
    }
}
