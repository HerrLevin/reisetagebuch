<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_pub_posts', function (Blueprint $table) {
            $table->json('extension_data')->nullable()->after('mentions');
        });
    }

    public function down(): void
    {
        Schema::table('activity_pub_posts', function (Blueprint $table) {
            $table->dropColumn('extension_data');
        });
    }
};
