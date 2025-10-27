<?php

namespace App\Enums;

enum TransportMode: string
{
    case WALK = 'WALK';
    case BIKE = 'BIKE';
    case RENTAL = 'RENTAL';
    case CAR = 'CAR';
    case CAR_PARKING = 'CAR_PARKING';
    case CAR_DROPOFF = 'CAR_DROPOFF';
    case ODM = 'ODM';
    case FLEX = 'FLEX';
    case TRANSIT = 'TRANSIT';
    case TRAM = 'TRAM';
    case SUBWAY = 'SUBWAY';
    case FERRY = 'FERRY';
    case AIRPLANE = 'AIRPLANE';
    case SUBURBAN = 'SUBURBAN';
    case BUS = 'BUS';
    case COACH = 'COACH';
    case RAIL = 'RAIL';
    case HIGHSPEED_RAIL = 'HIGHSPEED_RAIL';
    case LONG_DISTANCE = 'LONG_DISTANCE';
    case NIGHT_RAIL = 'NIGHT_RAIL';
    case REGIONAL_FAST_RAIL = 'REGIONAL_FAST_RAIL';
    case REGIONAL_RAIL = 'REGIONAL_RAIL';
    case CABLE_CAR = 'CABLE_CAR';
    case FUNICULAR = 'FUNICULAR';
    case AERIAL_LIFT = 'AERIAL_LIFT';
    case OTHER = 'OTHER';
    case AREAL_LIFT = 'AREAL_LIFT';
    case METRO = 'METRO';

    public function getTraewellingType(): string
    {
        return match ($this) {
            self::HIGHSPEED_RAIL, self::REGIONAL_FAST_RAIL, self::NIGHT_RAIL => 'nationalExpress',
            self::LONG_DISTANCE => 'regionalExp',
            self::METRO, self::SUBURBAN => 'suburban',
            self::BUS, self::COACH => 'bus',
            self::FERRY => 'ferry',
            self::SUBWAY => 'subway',
            self::TRAM, self::FUNICULAR, self::AERIAL_LIFT, self::AREAL_LIFT, self::CABLE_CAR => 'tram',
            self::AIRPLANE => 'plane',
            self::CAR, self::CAR_PARKING, self::ODM, self::RENTAL, self::FLEX => 'taxi',
            default => 'regional'
        };
    }
}
