<?php

namespace App\Http\Requests;

use App\Enums\TransportMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'StoreTripRequest',
    required: ['mode', 'origin', 'destination', 'departureTime', 'arrivalTime'],
    properties: [
        new OA\Property(property: 'mode', description: 'The mode of transport for the trip.', type: 'string', enum: TransportMode::class),
        new OA\Property(property: 'lineName', description: 'The name of the line.', type: 'string', nullable: true),
        new OA\Property(property: 'routeLongName', description: 'The long name of the route.', type: 'string', nullable: true),
        new OA\Property(property: 'tripShortName', description: 'The short name of the trip.', type: 'string', nullable: true),
        new OA\Property(property: 'displayName', description: 'The display name of the trip.', type: 'string', nullable: true),
        new OA\Property(property: 'origin', description: 'The origin location of the trip.', type: 'string'),
        new OA\Property(property: 'originType', description: 'The type of identifier used for the origin (id or identifier).', type: 'string', nullable: true),
        new OA\Property(property: 'destination', description: 'The destination location of the trip.', type: 'string'),
        new OA\Property(property: 'destinationType', description: 'The type of identifier used for the destination (id or identifier).', type: 'string', nullable: true),
        new OA\Property(property: 'departureTime', description: 'The departure time of the trip.', type: 'string', format: 'date-time'),
        new OA\Property(property: 'arrivalTime', description: 'The arrival time of the trip.', type: 'string', format: 'date-time'),
        new OA\Property(
            property: 'stops',
            description: 'An array of stops for the trip.',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'identifier', description: 'The identifier of the stop.', type: 'string'),
                    new OA\Property(property: 'identifierType', description: 'The type of identifier used for the stop (id or identifier).', type: 'string', nullable: true),
                    new OA\Property(property: 'order', description: 'The order of the stop in the trip sequence.', type: 'integer'),
                ],
                type: 'object',
            ),
            nullable: true,
        ),
    ],
    type: 'object'
)]
class StoreTripRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'mode' => ['required', Rule::enum(TransportMode::class)],
            'lineName' => 'nullable|string|max:255',
            'routeLongName' => 'nullable|string|max:255',
            'tripShortName' => 'nullable|string|max:255',
            'displayName' => 'nullable|string|max:255',
            'origin' => 'required|string|max:255',
            'originType' => 'sometimes|in:id,identifier',
            'destination' => 'required|string|max:255',
            'destinationType' => 'sometimes|in:id,identifier',
            'departureTime' => 'required|date',
            'arrivalTime' => 'required|date|after:origin.departure_time',
            'stops' => 'nullable|array',
            'stops.*.identifier' => 'required|string|max:255',
            'stops.*.identifierType' => 'sometimes|in:id,identifier',
            'stops.*.order' => 'required|numeric|min:1',
        ];
    }
}
