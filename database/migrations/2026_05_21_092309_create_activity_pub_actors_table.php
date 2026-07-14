<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_pub_actors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('actor_uri')->unique();
            $table->string('preferred_username')->nullable();
            $table->string('display_name')->nullable();
            $table->string('profile_url')->nullable();
            $table->string('inbox_url')->nullable();
            $table->string('shared_inbox_url')->nullable();
            $table->string('remote_icon_url')->nullable();
            $table->string('local_icon_path')->nullable();
            $table->string('icon_mime_type')->nullable();
            $table->string('icon_etag')->nullable();
            $table->timestamp('icon_fetched_at')->nullable();
            $table->timestamp('profile_fetched_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_pub_actors');
    }
};
