<?php

namespace App\Enums;

enum RouteSegmentSource: string
{
    case BROUTER = 'brouter';
    case TRANSITOUS = 'transitous';
    case MANUAL = 'manual';
}
