<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SettingsUpdateRequest',
    description: 'Request to update user settings',
    properties: [
        new OA\Property(
            property: 'motisRadius',
            description: 'Radius for Motis suggestions in meters (allowed values: 50, 100, 200, 300, 400, 500)',
            type: 'integer',
            example: 100,
            nullable: true,
            enum: [50, 100, 200, 300, 400, 500]
        ),
        new OA\Property(
            property: 'requiresFollowRequest',
            description: 'Requires following request',
            type: 'boolean',
            nullable: true
        ),
        new OA\Property(
            property: 'hidePostsAfter',
            description: 'Hide posts after x days',
            type: 'number',
            nullable: true,
            enum: [0.25, 0.5, 1, 2, 3, 4, 5, 6, 7, 14, 30],
        ),
    ]
)]
/**
 * @property-read ?int $motisRadius
 */
class SettingsUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'motisRadius' => ['nullable', 'integer', Rule::in([50, 100, 200, 300, 400, 500])],
            'requiresFollowRequest' => ['nullable', 'boolean'],
            'hidePostsAfter' => ['nullable', 'numeric', Rule::in([0.25, 0.5, 1, 2, 3, 4, 5, 6, 7, 14, 30])],
        ];
    }
}
