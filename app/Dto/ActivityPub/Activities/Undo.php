<?php

declare(strict_types=1);

namespace App\Dto\ActivityPub\Activities;

class Undo extends BaseActivity
{
    public readonly string $type;

    public function __construct()
    {
        $this->type = 'Undo';
    }
}
