<?php

use App\Models\Location;
use App\Models\Post;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transport_posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Post::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Location::class, 'origin_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Location::class, 'destination_id')->nullable()->constrained()->cascadeOnDelete();
            $table->dateTimeTz('departure');
            $table->dateTimeTz('arrival');
            $table->integer('departure_delay')->nullable();
            $table->integer('arrival_delay')->nullable();
            $table->string('mode');
            $table->string('line')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_posts');
    }
};
