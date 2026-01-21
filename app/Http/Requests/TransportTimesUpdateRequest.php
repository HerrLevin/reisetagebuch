<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TransportTimesUpdateRequest',
    properties: [
        new OA\Property(
            property: 'manualDepartureTime',
            description: 'The manually set departure time for the transport post.',
            type: 'string',
            format: 'date-time',
            example: '2024-08-01T10:00:00Z',
            nullable: true
        ),
        new OA\Property(
            property: 'manualArrivalTime',
            description: 'The manually set arrival time for the transport post.',
            type: 'string',
            format: 'date-time',
            example: '2024-08-01T12:00:00Z',
            nullable: true
        ),
    ],
    type: 'object'
)]

/**
 * * @property string $manualDepartureTime
 * * @property string $manualArrivalTime
 */
class TransportTimesUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'manualDepartureTime' => ['nullable', 'date'],
            'manualArrivalTime' => ['nullable', 'date', 'after:manualDepartureTime'],
        ];
    }
}
