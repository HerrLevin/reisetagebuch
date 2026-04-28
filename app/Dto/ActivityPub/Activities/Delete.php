<?php

namespace App\Dto\ActivityPub\Activities;

class Delete extends BaseActivity
{
    public readonly string $type;

    public string $actor;

    public array $to;

    public function __construct()
    {
        $this->type = 'Delete';
    }
}
