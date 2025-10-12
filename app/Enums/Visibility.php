<?php

namespace App\Enums;

enum Visibility: string
{
    case PUBLIC = 'public'; // Visible to everyone, listed publicly on profile and search
    case PRIVATE = 'private'; // Visible only to the owner, not listed publicly
    case UNLISTED = 'unlisted'; // Visible to anyone with the link, not listed publicly
}
