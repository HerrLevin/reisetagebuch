<?php

namespace App\Dto\ActivityPub\Activities;

class Accept extends BaseActivity
{
    public readonly string $type;

    public string $actor;

    public function __construct()
    {
        $this->type = 'Accept';
    }
}
