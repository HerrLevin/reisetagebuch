<?php

namespace App\Dto\ActivityPub\Activities;

class Reject extends BaseActivity
{
    public string $actor;

    public function __construct()
    {
        parent::__construct('Reject');
    }
}
