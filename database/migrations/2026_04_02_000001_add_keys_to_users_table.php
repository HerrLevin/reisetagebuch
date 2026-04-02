<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('public_key')->nullable();
            $table->text('private_key')->nullable();
        });

        // Generate RSA-2048 key pairs for all existing users
        $users = \App\Models\User::whereNull('public_key')->get();
        foreach ($users as $user) {
            $res = openssl_pkey_new([
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ]);
            openssl_pkey_export($res, $privateKey);
            $publicKey = openssl_pkey_get_details($res)['key'];

            $user->update([
                'public_key' => $publicKey,
                'private_key' => $privateKey,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['public_key', 'private_key']);
        });
    }
};
