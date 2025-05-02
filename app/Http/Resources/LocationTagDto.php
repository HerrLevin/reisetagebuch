<?php

namespace App\Http\Resources;

use App\Models\LocationTag;

class LocationTagDto
{
    public string $key;
    public string $value;

    public function __construct(LocationTag $locationTag)
    {
        $this->key = $locationTag->key;
        $this->value = $locationTag->value;
    }
}
