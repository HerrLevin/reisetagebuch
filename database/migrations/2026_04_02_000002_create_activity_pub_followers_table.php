<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_pub_followers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('follower_actor_id');
            $table->string('follower_inbox');
            $table->string('follower_shared_inbox')->nullable();
            $table->string('follower_username');
            $table->string('follower_display_name')->nullable();
            $table->string('follower_avatar')->nullable();
            $table->foreignUuid('followed_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['follower_actor_id', 'followed_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_pub_followers');
    }
};
