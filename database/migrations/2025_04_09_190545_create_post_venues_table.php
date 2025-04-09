<?php

use App\Models\Post;
use App\Models\Venue;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venue_posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Post::class);
            $table->foreignIdFor(Venue::class);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venue_posts');
    }
};
