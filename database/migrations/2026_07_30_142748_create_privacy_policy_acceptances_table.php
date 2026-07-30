<?php

use App\Models\PrivacyPolicy;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('privacy_policy_acceptances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(PrivacyPolicy::class)->constrained()->cascadeOnDelete();
            $table->timestamp('accepted_at');
            $table->timestamps();

            $table->unique(['user_id', 'privacy_policy_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('privacy_policy_acceptances');
    }
};
