<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_pub_remote_follows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(User::class, 'local_user_id')->constrained()->cascadeOnDelete();
            $table->string('remote_actor_id');
            $table->string('remote_actor_inbox_url');
            $table->string('remote_actor_shared_inbox_url')->nullable();
            $table->string('follow_activity_id');
            $table->string('state', 20)->default('pending');
            $table->timestamps();
            $table->unique(['local_user_id', 'remote_actor_id']);
            $table->index('local_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_pub_remote_follows');
    }
};
