<?php

use App\Jobs\CalculateStatisticsForUser;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->unique()->constrained()->onDelete('cascade');
            $table->unsignedInteger('posts_count')->default(0);
            $table->unsignedInteger('transport_posts_count')->default(0);
            $table->unsignedInteger('location_posts_count')->default(0);
            $table->unsignedInteger('followers_count')->default(0);
            $table->unsignedInteger('following_count')->default(0);
            $table->unsignedInteger('travelled_distance')->default(0)->comment('in meters');
            $table->unsignedInteger('travelled_duration')->default(0)->comment('in minutes');
            $table->unsignedInteger('visited_countries_count')->default(0);
            $table->unsignedInteger('visited_locations_count')->default(0);
            $table->timestamps();
        });

        User::all()->each(function (User $user) {
            $user->statistics()->create();
            CalculateStatisticsForUser::dispatch($user->id);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_statistics');
    }
};
