<?php

namespace App\Dto\ActivityPub\Activities;

class Reject extends BaseActivity
{
    public readonly string $type;

    public string $actor;

    public function __construct()
    {
        $this->type = 'Reject';
    }
}
