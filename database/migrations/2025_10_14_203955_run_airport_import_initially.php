<?php

use App\Jobs\FetchAirports;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }
        FetchAirports::dispatch();
    }

    public function down(): void
    {
        // No rollback needed for data import
    }
};
