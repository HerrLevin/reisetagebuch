<?php

declare(strict_types=1);

namespace App\Dto\ActivityPub\Objects;

class OrderedCollection extends BaseObject
{
    public readonly string $type;

    public int $totalItems;

    public string $first;

    public function __construct()
    {
        $this->type = 'OrderedCollection';
    }
}
