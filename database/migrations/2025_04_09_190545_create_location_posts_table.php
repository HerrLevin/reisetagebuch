<?php

use App\Models\Location;
use App\Models\Post;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('location_posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Post::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Location::class);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_posts');
    }
};
