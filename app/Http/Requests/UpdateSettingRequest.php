<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('edit settings') ?? false;
    }

    public function rules(): array
    {
        return [
            'value' => ['nullable', 'string', 'max:10000'],
            'type' => ['required', Rule::in(['string', 'text', 'boolean', 'integer', 'json', 'url', 'email'])],
            'group' => ['required', 'string', 'max:80'],
            'is_public' => ['required', 'boolean'],
        ];
    }
}
