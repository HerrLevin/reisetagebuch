<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TransportPostExitUpdateRequest',
    required: ['stopId'],
    properties: [
        new OA\Property(
            property: 'stopId',
            description: 'The ID of the transport trip stop, the user wants to exit at.',
            type: 'string',
            example: '123e4567-e89b-12d3-a456-426614174000'
        ),
    ],
    type: 'object'
)]

/**
 * @property string $stopId
 */
class TransportPostExitUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'stopId' => 'required|exists:transport_trip_stops,id',
        ];
    }
}
