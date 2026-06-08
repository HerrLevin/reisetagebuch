<?php

use App\Models\ActivityPubActor;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Add FK column first (nullable so existing rows don't violate the constraint)
        Schema::table('activity_pub_followers', function (Blueprint $table) {
            $table->foreignIdFor(ActivityPubActor::class)->nullable()->constrained()->nullOnDelete();
        });

        // Migrate existing follower rows: find or create an ActivityPubActor for each
        // follower_actor_id and populate activity_pub_actor_id.
        $followers = DB::table('activity_pub_followers')->get();
        foreach ($followers as $follower) {
            $actor = DB::table('activity_pub_actors')
                ->where('actor_uri', $follower->follower_actor_id)
                ->first();

            if ($actor) {
                // Actor already resolved — fill in inbox URLs if they are missing
                if (! $actor->inbox_url && $follower->follower_inbox_url) {
                    DB::table('activity_pub_actors')
                        ->where('id', $actor->id)
                        ->update([
                            'inbox_url' => $follower->follower_inbox_url,
                            'shared_inbox_url' => $follower->follower_shared_inbox_url,
                            'updated_at' => now(),
                        ]);
                }
                $actorId = $actor->id;
            } else {
                $actorId = Str::uuid()->toString();
                DB::table('activity_pub_actors')->insert([
                    'id' => $actorId,
                    'actor_uri' => $follower->follower_actor_id,
                    'inbox_url' => $follower->follower_inbox_url,
                    'shared_inbox_url' => $follower->follower_shared_inbox_url,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('activity_pub_followers')
                ->where('id', $follower->id)
                ->update(['activity_pub_actor_id' => $actorId]);
        }

        // Add index and remove now-redundant columns
        Schema::table('activity_pub_followers', function (Blueprint $table) {
            $table->index('activity_pub_actor_id');
            $table->dropIndex(['follower_shared_inbox_url']);
            $table->dropColumn(['follower_inbox_url', 'follower_shared_inbox_url']);
        });
    }

    public function down(): void
    {
        Schema::table('activity_pub_followers', function (Blueprint $table) {
            $table->string('follower_inbox_url')->nullable();
            $table->string('follower_shared_inbox_url')->nullable();
            $table->index(['follower_shared_inbox_url']);

            $table->dropForeign(['activity_pub_actor_id']);
            $table->dropIndex(['activity_pub_actor_id']);
            $table->dropColumn('activity_pub_actor_id');
        });
    }
};
