<?php

namespace App\Enums\PostMetaInfo;

enum MetaInfoKey: string
{
    case TRAEWELLING_TRIP_ID = 'rtb:traewelling_trip_id';
    case TRAVEL_REASON = 'rtb:travel_reason';

    public function valueType(): MetaInfoValueType
    {
        return match ($this) {
            self::TRAEWELLING_TRIP_ID => MetaInfoValueType::STRING,
            self::TRAVEL_REASON => MetaInfoValueType::ENUM,
        };
    }

    public function getEnumClass(): ?string
    {
        return match ($this) {
            self::TRAVEL_REASON => TravelReason::class,
            default => null,
        };
    }
}
