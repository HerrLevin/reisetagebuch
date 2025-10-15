<?php

namespace App\Enums;

enum TransportMode: string
{
    case TRANSIT = 'TRANSIT';
    case WALK = 'WALK';
    case BIKE = 'BIKE';
    case RENTAL = 'RENTAL';
    case CAR = 'CAR';
    case CAR_PARKING = 'CAR_PARKING';
    case ODM = 'ODM';
    case TRAM = 'TRAM';
    case SUBWAY = 'SUBWAY';
    case FERRY = 'FERRY';
    case AIRPLANE = 'AIRPLANE';
    case METRO = 'METRO';
    case BUS = 'BUS';
    case COACH = 'COACH';
    case RAIL = 'RAIL';
    case HIGHSPEED_RAIL = 'HIGHSPEED_RAIL';
    case LONG_DISTANCE = 'LONG_DISTANCE';
    case NIGHT_RAIL = 'NIGHT_RAIL';
    case REGIONAL_FAST_RAIL = 'REGIONAL_FAST_RAIL';
    case REGIONAL_RAIL = 'REGIONAL_RAIL';
    case FUNICULAR = 'FUNICULAR';
    case OTHER = 'OTHER';
    case FLEX = 'FLEX';

    public function getTraewellingType(): string
    {
        return match ($this) {
            self::HIGHSPEED_RAIL, self::REGIONAL_FAST_RAIL, self::NIGHT_RAIL => 'nationalExpress',
            self::LONG_DISTANCE => 'regionalExp',
            self::METRO => 'suburban',
            self::BUS, self::COACH => 'bus',
            self::FERRY => 'ferry',
            self::SUBWAY => 'subway',
            self::TRAM, self::FUNICULAR => 'tram',
            self::AIRPLANE => 'plane',
            self::CAR, self::CAR_PARKING, self::ODM, self::RENTAL, self::FLEX => 'taxi',
            default => 'regional'
        };
    }
}
