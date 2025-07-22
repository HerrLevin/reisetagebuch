<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StopoverRequest
 *
 *
 * @property string $tripId
 * @property string $headSign
 * @property string $routeName
 * @property string $startId
 * @property string $startTime
 */
class StopoverRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'tripId' => 'required|string',
            'startId' => 'required|string',
            'startTime' => 'required|date',
        ];
    }
}
