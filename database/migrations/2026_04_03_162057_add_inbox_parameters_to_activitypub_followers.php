<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('activity_pub_followers', function (Blueprint $table) {
            $table->string('follower_inbox_url')->nullable()->after('follower_actor_id');
            $table->string('follower_shared_inbox_url')->nullable()->after('follower_inbox_url');
            $table->index(['follower_shared_inbox_url']);
        });
    }

    public function down(): void
    {
        Schema::table('activity_pub_followers', function (Blueprint $table) {
            $table->dropIndex(['follower_shared_inbox_url']);
            $table->dropColumn('follower_inbox_url');
            $table->dropColumn('follower_shared_inbox_url');
        });
    }
};
