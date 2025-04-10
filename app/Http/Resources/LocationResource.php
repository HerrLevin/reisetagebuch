<?php

namespace App\Http\Resources;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        /** @var Location $this */
        return [
            'id' => $this->id,
            'name' => $this->name,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'distance' => $this->distance ?? null,
            'tags' => LocationTagResource::collection($this->tags),
        ];
    }
}
