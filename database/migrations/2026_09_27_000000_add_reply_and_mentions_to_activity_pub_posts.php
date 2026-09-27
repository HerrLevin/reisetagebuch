<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_pub_posts', function (Blueprint $table) {
            $table->string('in_reply_to')->nullable()->after('content');
            $table->json('mentions')->nullable()->after('in_reply_to');
        });
    }

    public function down(): void
    {
        Schema::table('activity_pub_posts', function (Blueprint $table) {
            $table->dropColumn(['in_reply_to', 'mentions']);
        });
    }
};
