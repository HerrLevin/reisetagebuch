<?php

use App\Models\ActivityPubPost;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_pub_post_likes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(ActivityPubPost::class)->constrained()->cascadeOnDelete();
            $table->string('activity_id')->nullable();
            $table->unique(['user_id', 'activity_pub_post_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_pub_post_likes');
    }
};
