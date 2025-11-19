<?php

use App\Models\Post;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // set published_at to created_at for existing records
        Post::with('transportPost.originStop')->chunk(100, function ($posts) {
            /** @var Post $post */
            foreach ($posts as $post) {
                $publishedAt = $post->created_at;
                if ($post->transportPost !== null) {
                    $transportPost = $post->transportPost;
                    $publishedAt = $transportPost->originStop->departure_time ?? $transportPost->originStop->arrival_time ?? $publishedAt;
                }

                $post->published_at = $publishedAt;
                $post->save();
            }
        });
    }
};
