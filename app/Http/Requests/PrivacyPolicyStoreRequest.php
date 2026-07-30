<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PrivacyPolicyStoreRequest',
    description: 'Request to publish a new privacy policy version',
    required: ['content', 'validFrom'],
    properties: [
        new OA\Property(
            property: 'content',
            description: 'The privacy policy content',
            type: 'string',
        ),
        new OA\Property(
            property: 'validFrom',
            description: 'The point in time from which this version takes effect. Must not be in the past.',
            type: 'string',
            format: 'date-time',
        ),
    ]
)]
class PrivacyPolicyStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:20000'],
            'validFrom' => ['required', 'date', 'after_or_equal:now'],
        ];
    }
}
