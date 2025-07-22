<?php

namespace App\Http\Requests;

use App\Enums\DefaultNewPostView;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property-read ?DefaultNewPostView $defaultNewPostView
 */
class SettingsUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'defaultNewPostView' => ['nullable', 'string', Rule::enum(DefaultNewPostView::class)],
        ];
    }
}
