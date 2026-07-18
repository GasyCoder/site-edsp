<?php

namespace App\Http\Requests;

use App\Rules\PublicSlug;
use Illuminate\Validation\Rule;

class UpdateProgramRequest extends StoreProgramRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('edit programs') ?? false;
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $rules['slug'] = ['required', new PublicSlug, 'max:180', Rule::unique('programs', 'slug')->ignore($this->route('program'))];

        return $rules;
    }
}
