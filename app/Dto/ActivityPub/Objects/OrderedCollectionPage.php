<?php

namespace App\Dto\ActivityPub\Objects;

class OrderedCollectionPage extends BaseObject
{
    public string $partOf;

    public array $orderedItems;

    public string $next;

    public string $prev;

    public function __construct()
    {
        parent::__construct('OrderedCollectionPage');
    }
}
