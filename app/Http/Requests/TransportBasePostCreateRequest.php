<?php

namespace App\Http\Requests;

use App\Enums\PostMetaInfo\TravelRole;
use Illuminate\Validation\Rule;

/**
 * @property string $body
 * @property string $tripId
 * @property string $startId
 * @property string $startTime
 * @property string $stopId
 * @property string $stopTime
 * @property array|null $vehicleIds
 * @property TravelRole|null $travelRole
 * @property string|null $metaTripId
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
            'vehicleIds' => 'array',
            'vehicleIds.*' => 'string|nullable',
            'travelRole' => ['nullable', Rule::enum(TravelRole::class)],
            'metaTripId' => 'nullable|string',
        ], parent::rules());
    }
}
