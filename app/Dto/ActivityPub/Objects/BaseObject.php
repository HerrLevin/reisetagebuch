<?php

namespace App\Dto\ActivityPub\Objects;

use App\Traits\ActivityPubContextable;
use App\Traits\JsonResponseObject;

abstract class BaseObject
{
    use ActivityPubContextable, JsonResponseObject;

    public string $id;

    public readonly string $type;
}
