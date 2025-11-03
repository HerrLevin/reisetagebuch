<?php

use App\Models\Post;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // set published_at to created_at for existing records
        Post::with('transportPost.originStop')->chunk(100, function ($posts) {
            /** @var Post $post */
            foreach ($posts as $post) {
                if ($post->published_at === null) {
                    $publishedAt = $post->created_at;
                    if ($post->transportPost !== null) {
                        $transportPost = $post->transportPost;
                        $publishedAt = $transportPost->originStop->departure_time ?? $transportPost->originStop->arrival_time ?? $publishedAt;
                    }

                    $post->published_at = $publishedAt;
                    $post->save();
                }
            }
        });
        DB::table('posts')->update(['published_at' => DB::raw('created_at')]);

        // make published_at non-nullable
        Schema::table('posts', function (Blueprint $table) {
            $table->timestamp('published_at')
                ->nullable(false)
                ->default(DB::raw('CURRENT_TIMESTAMP'))
                ->change();

            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_published_at_index');
            $table->timestamp('published_at')->nullable()->change();
        });
    }
};
