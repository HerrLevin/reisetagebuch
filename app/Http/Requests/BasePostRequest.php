<?php

namespace App\Http\Requests;

use App\Enums\PostMetaInfo\TravelReason;
use App\Enums\Visibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BasePostRequest',
    required: ['visibility', 'travelReason'],
    properties: [
        new OA\Property(
            property: 'id',
            description: 'ID of the pot. Required if post is edited.',
            type: 'string',
            format: 'uuid',
        ),
        new OA\Property(
            property: 'body',
        ),
        new OA\Property(
            property: 'visibility',
            ref: Visibility::class,
        ),
        new OA\Property(
            property: 'travelReason',
            ref: TravelReason::class,
        ),
        new OA\Property(
            property: 'tags',
            type: 'array',
            items: new OA\Items(type: 'string')
        ),
        new OA\Property(
            property: 'vehicleIds',
            type: 'array',
            items: new OA\Items(type: 'string')
        ),
    ],
    type: 'object'
)]
class BasePostRequest extends FormRequest
{
    protected array $extraBodyRules = ['required_without:id'];

    public function rules(): array
    {
        return [
            'id' => ['sometimes', 'uuid', ' exists:posts,id'],
            'body' => $this->bodyRule(),
            'visibility' => ['required_without:id', Rule::enum(Visibility::class)],
            'tags' => ['sometimes', 'max:5', 'array'],
            'tags.*' => ['string', 'regex:/^[\w\_\- ]+$/'],
            'travelReason' => Rule::enum(TravelReason::class),
            'vehicleIds' => ['sometimes', 'array'],
            'vehicleIds.*' => ['string'],
        ];
    }

    protected function bodyRule(): array
    {
        $baseRules = ['string', 'max:255'];

        return array_merge($baseRules, $this->extraBodyRules);
    }
}
