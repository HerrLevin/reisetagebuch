<?php

namespace App\Traits;

trait ActivityPubContextable
{
    protected array|string|null $context = null;

    public function setContext(array|string|null $context = 'https://www.w3.org/ns/activitystreams'): void
    {
        $this->context = $context;
    }

    protected function prependRaw(): array
    {
        if ($this->context) {
            return [
                '@context' => $this->context,
            ];
        }

        return [];
    }
}
