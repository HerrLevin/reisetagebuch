<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'origin_user_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'target_user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['origin_user_id', 'target_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};
