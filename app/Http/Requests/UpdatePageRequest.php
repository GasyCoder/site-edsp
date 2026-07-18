<?php

namespace App\Http\Requests;

use App\Rules\PublicSlug;
use Illuminate\Validation\Rule;

class UpdatePageRequest extends StorePageRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('edit pages') ?? false;
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $rules['slug'] = ['required', new PublicSlug, 'max:180', Rule::unique('pages', 'slug')->ignore($this->route('page'))];

        return $rules;
    }
}
