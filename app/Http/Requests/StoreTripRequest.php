<?php

namespace App\Http\Requests;

use App\Enums\TransportMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property-read string $mode
 * @property-read string|null $lineName
 * @property-read string|null $routeLongName
 * @property-read string|null $tripShortName
 * @property-read string|null $displayName
 * @property-read string $origin
 * @property-read string $originType
 * @property-read string $destination
 * @property-read string $destinationType
 * @property-read string $departureTime
 * @property-read string $arrivalTime
 */
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
