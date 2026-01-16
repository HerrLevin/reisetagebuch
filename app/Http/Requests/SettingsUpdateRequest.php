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
            description: 'Radius for Motis suggestions in meters (allowed values: 50, 100, 200, 500)',
            type: 'integer',
            nullable: true
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
            'motisRadius' => ['nullable', 'integer', Rule::in([50, 100, 200, 500])],
        ];
    }
}
