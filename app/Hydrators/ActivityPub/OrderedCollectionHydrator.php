<?php

declare(strict_types=1);

namespace App\Hydrators\ActivityPub;

use App\Dto\ActivityPub\Objects\OrderedCollection;

class OrderedCollectionHydrator
{
    public function hydrate(string $id, int $totalItems, ?string $first = null): OrderedCollection
    {
        $collection = new OrderedCollection;
        $collection->id = $id;
        $collection->totalItems = $totalItems;
        if ($first) {
            $collection->first = $first;
        }
        $collection->setContext();

        return $collection;
    }
}
