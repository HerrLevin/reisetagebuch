<?php

namespace App\Dto\MotisApi;

enum LocationType: string
{
    case ADDRESS = 'ADDRESS';
    case PLACE = 'PLACE';
    case STOP = 'STOP';
}
