<?php

declare(strict_types=1);

namespace App\Dto\ActivityPub\Activities;

class Like extends BaseActivity
{
    public function __construct()
    {
        parent::__construct('Like');
    }
}
