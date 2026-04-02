<?php

namespace App\Enums;

enum ActivityPubInteractionType: string
{
    case LIKE = 'like';
    case BOOST = 'boost';
    case REPLY = 'reply';
}
