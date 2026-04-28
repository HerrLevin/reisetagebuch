<?php

namespace App\Hydrators\ActivityPub;

use App\Dto\ActivityPub\Objects\OrderedCollectionPage;

class OrderedCollectionPageHydrator
{
    public function hydrate(string $id, string $partOf, array $items, ?string $next, ?string $prev): OrderedCollectionPage
    {
        $collection = new OrderedCollectionPage;
        $collection->id = $id;
        $collection->partOf = $partOf;
        $collection->orderedItems = $items;
        if ($next !== null) {
            $collection->next = $next;
        }
        if ($prev !== null) {
            $collection->prev = $prev;
        }
        $collection->setContext();

        return $collection;
    }
}
