<?php

namespace App\Traits;

trait ActivityPubContextable
{
    private array|string|null $__context = null;

    public function setContext(array|string|null $__context = 'https://www.w3.org/ns/activitystreams'): void
    {
        $this->__context = $__context;
    }

    protected function prependRaw(): array
    {
        if ($this->__context) {
            return [
                '@context' => $this->__context,
            ];
        }

        return [];
    }
}
