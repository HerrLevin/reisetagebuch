<?php

namespace App\Dto\ActivityPub\Activities;

class Accept extends BaseActivity
{
    public string $actor;

    public function __construct()
    {
        parent::__construct('Accept');
    }
}
