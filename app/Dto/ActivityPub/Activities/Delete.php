<?php

namespace App\Dto\ActivityPub\Activities;

class Delete extends BaseActivity
{
    public string $actor;

    public array $to;

    public function __construct()
    {
        parent::__construct('Delete');
    }
}
