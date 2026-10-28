<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * RFC 7591 client metadata, required for dynamically registered clients.
     */
    public function up(): void
    {
        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->string('client_uri', 2048)->nullable();
            $table->string('policy_uri', 2048)->nullable();
            $table->string('tos_uri', 2048)->nullable();
            $table->string('logo_uri', 2048)->nullable();
            $table->text('contacts')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->dropColumn(['client_uri', 'policy_uri', 'tos_uri', 'logo_uri', 'contacts']);
        });
    }
};
