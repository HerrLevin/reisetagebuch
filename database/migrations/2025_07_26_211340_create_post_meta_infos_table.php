<?php

use App\Models\Post;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_meta_infos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Post::class)->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->text('value')->nullable();
            $table->unique(['post_id', 'key'], 'post_meta_infos_unique_key');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_meta_infos');
    }
};
