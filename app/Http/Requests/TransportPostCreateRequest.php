<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string $body
 * @property string $tripId
 * @property string $startId
 * @property string $startTime
 * @property string $stopId
 * @property string $stopTime
 */
class TransportPostCreateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'body' => 'nullable|string|max:255',
            'tripId' => 'required',
            'startId' => 'required',
            'startTime' => 'required',
            'stopId' => 'required',
            'stopTime' => 'required',
        ];
    }
}
