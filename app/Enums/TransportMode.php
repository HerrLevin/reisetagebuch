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
    case OTHER = 'OTHER';
}
