<?php

namespace App\Repositories;

use App\Enums\PostMetaInfo\MetaInfoKey;
use App\Enums\PostMetaInfo\MetaInfoValueType;
use App\Models\Post;
use App\Models\PostMetaInfo;

class PostMetaInfoRepository
{
    public function updateOrCreateMetaInfo(Post $post, MetaInfoKey $key, string $value): PostMetaInfo
    {
        if ($key->valueType() === MetaInfoValueType::ENUM) {
            $value = $key->getEnumClass()::tryFrom($value);
            if (! $value) {
                throw new \InvalidArgumentException("Invalid enum value for key {$key->value}");
            }
            $value = $value->value;
        }

        return PostMetaInfo::updateOrCreate(
            [
                'post_id' => $post->id,
                'key' => $key->value,
            ],
            [
                'value' => $value,
            ]
        );
    }

    public function getMetaInfoValue(Post $post, MetaInfoKey $key): ?string
    {
        $metaInfo = PostMetaInfo::where('post_id', $post->id)
            ->where('key', $key->value)
            ->first();

        return $metaInfo?->value;
    }
}
