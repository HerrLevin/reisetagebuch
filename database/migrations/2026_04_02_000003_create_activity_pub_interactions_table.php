<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_pub_interactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->string('activity_id')->unique();
            $table->string('actor_id');
            $table->string('actor_username');
            $table->string('actor_display_name')->nullable();
            $table->string('actor_avatar')->nullable();
            $table->string('actor_instance');
            $table->foreignUuid('post_id')->constrained('posts')->cascadeOnDelete();
            $table->text('content')->nullable();
            $table->string('remote_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_pub_interactions');
    }
};
