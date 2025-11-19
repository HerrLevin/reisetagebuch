<?php

namespace App\Enums\PostMetaInfo;

enum MetaInfoKey: string
{
    case TRAEWELLING_TRIP_ID = 'rtb:traewelling_trip_id';
    case TRAVEL_REASON = 'rtb:travel_reason';
    case TRAVEL_ROLE = 'rtb:travel_role';
    case TRIP_ID = 'rtb:trip_id';
    case VEHICLE_ID = 'rtb:vehicle_id';

    public function valueType(): MetaInfoValueType
    {
        return match ($this) {
            self::TRAEWELLING_TRIP_ID, self::TRIP_ID => MetaInfoValueType::STRING,
            self::TRAVEL_REASON, self::TRAVEL_ROLE => MetaInfoValueType::ENUM,
            self::VEHICLE_ID => MetaInfoValueType::STRING_LIST,
        };
    }

    public function getEnumClass(): ?string
    {
        return match ($this) {
            self::TRAVEL_REASON => TravelReason::class,
            self::TRAVEL_ROLE => TravelRole::class,
            default => null,
        };
    }
}
