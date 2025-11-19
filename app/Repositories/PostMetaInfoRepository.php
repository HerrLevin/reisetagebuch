<?php

namespace App\Repositories;

use App\Enums\PostMetaInfo\MetaInfoKey;
use App\Enums\PostMetaInfo\MetaInfoValueType;
use App\Enums\PostMetaInfo\TravelReason;
use App\Models\Post;
use App\Models\PostMetaInfo;

class PostMetaInfoRepository
{
    public function setTravelReason(Post $post, TravelReason $reason): PostMetaInfo
    {
        return $this->updateOrCreateMetaInfo($post, MetaInfoKey::TRAVEL_REASON, $reason->value);
    }

    public function setVehicleIds(Post $post, array $vehicleIds): void
    {
        PostMetaInfo::where('post_id', $post->id)
            ->where('key', MetaInfoKey::VEHICLE_ID->value)
            ->delete();

        foreach ($vehicleIds as $index => $vehicleId) {
            if (filled($vehicleId)) {
                $this->updateOrCreateMetaInfo($post, MetaInfoKey::VEHICLE_ID, $vehicleId, $index);
            }
        }
    }

    public function updateOrCreateMetaInfo(Post $post, MetaInfoKey $key, ?string $value, ?int $order = null): ?PostMetaInfo
    {
        if ($value === null) {
            PostMetaInfo::where('post_id', $post->id)
                ->where('key', $key->value)
                ->when($order !== null, function ($query) use ($order) {
                    $query->where('order', $order);
                })
                ->delete();

            return null;
        }

        if ($key->valueType() === MetaInfoValueType::ENUM) {
            $value = $key->getEnumClass()::tryFrom($value);
            if (! $value) {
                throw new \InvalidArgumentException("Invalid enum value for key {$key->value}");
            }
            $value = $value->value;
        }

        if ($key->valueType() === MetaInfoValueType::STRING_LIST && $order === null) {
            throw new \InvalidArgumentException('Order must be provided for STRING_LIST type');
        }

        return PostMetaInfo::updateOrCreate(
            [
                'post_id' => $post->id,
                'key' => $key->value,
                'order' => $order,
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
