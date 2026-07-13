<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_pub_inbox_items', function (Blueprint $table) {
            $table->id();
            $table->string('activity_id');
            $table->string('actor_id');
            $table->string('activity_type', 50)->nullable();
            $table->unique(['activity_id', 'actor_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_pub_inbox_items');
    }
};
