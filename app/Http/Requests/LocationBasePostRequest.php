<?php

namespace App\Http\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LocationPostRequest',
    required: ['location', 'visitedAt'],
    properties: [
        new OA\Property(
            property: 'location',
            type: 'string',
            format: 'uuid'
        ),
        new OA\Property(
            property: 'visitedAt',
            type: 'string',
            format: 'date-time',
            nullable: true,
        ),
    ],
    type: 'object'
)]
class LocationBasePostRequest extends BasePostRequest
{
    public function rules(): array
    {
        $this->extraBodyRules = ['nullable'];

        return array_merge([
            'location' => 'required|exists:locations,id',
            'visitedAt' => ['required', 'date', 'nullable'],
        ], parent::rules());
    }
}
