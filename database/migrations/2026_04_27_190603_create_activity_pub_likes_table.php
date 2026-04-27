<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_pub_likes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('actor_id');
            $table->foreignUuid('post_id')->constrained('posts')->cascadeOnDelete();
            $table->string('activity_id')->nullable();
            $table->unique(['actor_id', 'post_id']);
            $table->index('post_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_pub_likes');
    }
};
