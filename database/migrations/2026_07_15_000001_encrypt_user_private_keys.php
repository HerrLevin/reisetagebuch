<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->whereNotNull('private_key')->orderBy('id')->chunkById(100, function ($users) {
            foreach ($users as $user) {
                DB::table('users')->where('id', $user->id)->update([
                    'private_key' => Crypt::encryptString($user->private_key),
                ]);
            }
        });
    }

    public function down(): void
    {
        DB::table('users')->whereNotNull('private_key')->orderBy('id')->chunkById(100, function ($users) {
            foreach ($users as $user) {
                try {
                    $decrypted = Crypt::decryptString($user->private_key);
                } catch (DecryptException) {
                    continue;
                }

                DB::table('users')->where('id', $user->id)->update([
                    'private_key' => $decrypted,
                ]);
            }
        });
    }
};
