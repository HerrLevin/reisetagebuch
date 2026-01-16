<?php

namespace App\Http\Requests;

use App\Models\Invite;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'StoreInviteCodeRequest',
    required: [],
    properties: [
        new OA\Property(property: 'expires_at', description: 'The expiration date of the invite code', type: 'string', format: 'date-time', nullable: true),
    ],
    type: 'object'
)]
class StoreInviteCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! config('app.invite.enabled')) {
            return false;
        }

        return auth()->user()->can('create', Invite::class);
    }

    public function rules(): array
    {
        return [
            'expires_at' => 'nullable|date',
        ];
    }
}
