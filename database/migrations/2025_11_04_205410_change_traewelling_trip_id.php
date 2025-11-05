<?php

use App\Enums\PostMetaInfo\MetaInfoKey;
use App\Models\PostMetaInfo;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        PostMetaInfo::where('key', 'traewelling_trip_id')->update(['key' => MetaInfoKey::TRAEWELLING_TRIP_ID]);
    }

    public function down(): void
    {
        PostMetaInfo::where('key', MetaInfoKey::TRAEWELLING_TRIP_ID)->update(['key' => 'traewelling_trip_id']);
    }
};
