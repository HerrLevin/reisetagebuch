<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_pub_followers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('follower_actor_id'); // Actor ID of the follower (can be remote)
            $table->foreignIdFor(User::class, 'followed_user_id')->constrained();
            $table->unique(['follower_actor_id', 'followed_user_id']); // Prevent duplicate follows
            $table->timestamps();
            $table->index(['followed_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_pub_followers');
    }
};
