<?php

namespace App\Http\Requests;

use App\Models\Invite;
use Illuminate\Foundation\Http\FormRequest;

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
