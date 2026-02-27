<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('oauth_clients')->insert([
            'id' => Str::uuid()->toString(),
            'owner_id' => null,
            'owner_type' => null,
            'name' => 'Reisetagebuch Personal Access Client',
            'secret' => null,
            'provider' => 'users',
            'redirect_uris' => '[]',
            'grant_types' => '["personal_access"]',
            'revoked' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('oauth_clients')
            ->where('name', 'Reisetagebuch Personal Access Client')
            ->where('grant_types', '["personal_access"]')
            ->delete();
    }

    public function getConnection(): ?string
    {
        return $this->connection ?? config('passport.connection');
    }
};
