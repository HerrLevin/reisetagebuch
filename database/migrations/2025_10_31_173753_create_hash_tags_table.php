<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hash_tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->string('value');
            $table->mediumInteger('relevance')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hash_tags');
    }
};
