<?php

namespace App\Http\Resources;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        /** @var Post $this */
        return [
            'id' => $this->id,
            'user' => new UserResource($this->user),
            'body' => $this->body,
            'location' => $this->locationPost ? new LocationResource($this->locationPost->location) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
