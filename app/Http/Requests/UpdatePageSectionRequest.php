<?php

namespace App\Http\Requests;

use App\Rules\SafeUrl;
use App\Services\RichTextSanitizer;
use App\Support\SectionSettingsSchema;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePageSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('edit pages') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:180'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:30000'],
            'image_id' => ['nullable', 'integer', Rule::exists('media', 'id')->where(fn ($query) => $query->where('mime_type', 'like', 'image/%')->whereNotNull('alt_text')->where('alt_text', '!=', ''))],
            'button_text' => ['nullable', 'string', 'max:80'],
            'button_url' => ['nullable', 'max:2048', new SafeUrl],
            'position' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'is_visible' => ['required', 'boolean'],
            'settings' => ['nullable', 'array:'.implode(',', SectionSettingsSchema::keys())],
            ...SectionSettingsSchema::validationRules(),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['content' => app(RichTextSanitizer::class)->clean($this->input('content'))]);
    }
}
