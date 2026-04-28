<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('avatar_mime_type')->nullable();
            $table->string('header_mime_type')->nullable();
        });

        $disk = Storage::disk('public');

        DB::table('profiles')->chunkById(100, function ($profiles) use ($disk) {
            foreach ($profiles as $profile) {
                $avatar = null;
                $header = null;
                if ($profile->avatar && $disk->exists($profile->avatar)) {
                    $avatar = $disk->mimeType($profile->avatar);
                }
                if ($profile->header && $disk->exists($profile->header)) {
                    $header = $disk->mimeType($profile->header);
                }

                if ($avatar || $header) {
                    DB::table('profiles')
                        ->where('id', $profile->id)
                        ->update([
                            'header_mime_type' => $header ?: null,
                            'avatar_mime_type' => $avatar ?: null,
                        ]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('avatar_mime_type');
            $table->dropColumn('header_mime_type');
        });
    }
};
