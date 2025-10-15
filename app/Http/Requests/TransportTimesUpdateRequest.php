<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
