<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ImprintUpdateRequest',
    description: 'Request to update the instance imprint',
    required: ['content'],
    properties: [
        new OA\Property(
            property: 'content',
            description: 'The imprint content',
            type: 'string',
            nullable: true,
        ),
    ]
)]
class ImprintUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'content' => ['nullable', 'string', 'max:20000'],
        ];
    }
}
