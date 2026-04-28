<?php

namespace App\Dto\ActivityPub\Activities;

class Update extends BaseActivity
{
    public readonly string $type;

    public string $actor;

    public array $to;

    public array $cc;

    public function __construct()
    {
        $this->type = 'Update';
    }
}
