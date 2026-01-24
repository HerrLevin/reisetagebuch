<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Notifications\DatabaseNotification;

return new class extends Migration
{
    public function up(): void
    {
        DatabaseNotification::where('type', 'post-liked')->delete();
    }
};
