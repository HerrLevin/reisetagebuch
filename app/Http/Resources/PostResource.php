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
            'start' => $this->transportPost ? new LocationResource($this->transportPost->origin) : null,
            'stop' => $this->transportPost ? new LocationResource($this->transportPost->destination) : null,
            'start_time' => $this->transportPost ? $this->transportPost->departure : null,
            'stop_time' => $this->transportPost ? $this->transportPost->arrival : null,
            'mode' => $this->transportPost ? $this->transportPost->mode : null,
            'line' => $this->transportPost ? $this->transportPost->line : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
