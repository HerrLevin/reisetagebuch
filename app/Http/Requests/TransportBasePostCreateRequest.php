<?php

namespace App\Http\Requests;

/**
 * @property string $body
 * @property string $tripId
 * @property string $startId
 * @property string $startTime
 * @property string $stopId
 * @property string $stopTime
 */
class TransportBasePostCreateRequest extends BasePostRequest
{
    public function rules(): array
    {
        $this->extraBodyRules = ['nullable'];

        return array_merge([
            'tripId' => 'required',
            'startId' => 'required',
            'startTime' => 'required',
            'stopId' => 'required',
            'stopTime' => 'required',
        ], parent::rules());
    }
}
