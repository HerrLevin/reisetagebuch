<?php

use App\Models\ActivityPubActor;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_pub_posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(ActivityPubActor::class)->constrained()->cascadeOnDelete();
            $table->string('activity_id')->unique();
            $table->string('url')->nullable();
            $table->text('content')->nullable();
            $table->timestamp('published_at');
            $table->timestamps();
            $table->index('activity_pub_actor_id');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_pub_posts');
    }
};
