<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'StopoverLogRequest',
    required: ['timestamp'],
    properties: [
        new OA\Property(
            property: 'timestamp',
            description: 'The client-observed time at which the arrival/departure occurred, in ISO 8601 format. Using the time observed on the device (rather than the time the request reaches the server) keeps the logged time accurate under poor network reception.',
            type: 'string',
            format: 'date-time',
            example: '2024-08-01T10:00:00Z'
        ),
    ],
    type: 'object'
)]

/**
 * @property string $timestamp
 */
class StopoverLogRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'timestamp' => ['required', 'date'],
        ];
    }
}
