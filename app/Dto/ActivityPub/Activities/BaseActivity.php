<?php

namespace App\Dto\ActivityPub\Activities;

use App\Dto\ActivityPub\Objects\BaseObject;
use App\Traits\ActivityPubContextable;
use App\Traits\JsonResponseObject;

class BaseActivity
{
    use ActivityPubContextable, JsonResponseObject;

    public string $id;

    public readonly string $type;

    public string $actor;

    public string $published;

    public BaseObject|array $object;
}
