<?php

namespace App\Http\Resources;

use App\Models\LocationTag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationTagResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var LocationTag $this */
        return [
            'key' => $this->key,
            'value' => $this->value,
        ];
    }
}
