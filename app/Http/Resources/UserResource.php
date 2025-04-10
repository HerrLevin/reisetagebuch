<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        /** @var User $this */
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
