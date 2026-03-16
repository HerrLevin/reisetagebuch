<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TransportTrackUploadRequest',
    description: 'Request to upload a GPX or GeoJSON track file',
    required: ['track'],
    properties: [
        new OA\Property(
            property: 'track',
            description: 'GPX or GeoJSON track file (max 5MB)',
            type: 'string',
            format: 'binary',
            nullable: false
        ),
    ],
    type: 'object'
)]
class TransportTrackUploadRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'track' => ['required', 'extensions:gpx,geojson,json', 'max:10240'],
        ];
    }
}
