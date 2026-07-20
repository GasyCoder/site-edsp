<?php

namespace App\Http\Requests;

use App\Enums\ContentStatus;
use App\Rules\PublicSlug;
use App\Rules\SafeUrl;
use App\Services\RichTextSanitizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create programs') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $fields = ['description', 'objectives', 'admission_requirements', 'skills', 'careers', 'curriculum'];
        $values = app(RichTextSanitizer::class)->cleanFields($this->all(), $fields);
        if (blank($values['slug'] ?? null) && filled($values['title'] ?? null)) {
            $values['slug'] = Str::slug((string) $values['title']);
        }
        $this->replace($values);
    }

    public function rules(): array
    {
        return [
            'mention_id' => ['required', 'integer', 'exists:mentions,id'],
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['required', new PublicSlug, 'max:180', 'unique:programs,slug'],
            'level' => ['sometimes', 'string', 'max:100'],
            'domain' => ['nullable', 'string', 'max:180'],
            'mention' => ['nullable', 'string', 'max:180'],
            'track' => ['nullable', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:30000'],
            'objectives' => ['nullable', 'string', 'max:50000'],
            'admission_requirements' => ['nullable', 'string', 'max:50000'],
            'skills' => ['nullable', 'string', 'max:50000'],
            'careers' => ['nullable', 'string', 'max:50000'],
            'duration' => ['nullable', 'string', 'max:100'],
            'curriculum' => ['nullable', 'string', 'max:100000'],
            'manager' => ['nullable', 'string', 'max:180'],
            'image_id' => ['nullable', 'integer', Rule::exists('media', 'id')->where(fn ($query) => $query->where('mime_type', 'like', 'image/%')->whereNotNull('alt_text')->where('alt_text', '!=', ''))],
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'position' => ['required', 'integer', 'min:0', 'max:65535'],
            'meta_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:180'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'canonical_url' => ['nullable', new SafeUrl],
            'robots_index' => ['required', 'boolean'],
            'robots_follow' => ['required', 'boolean'],
            'og_title' => ['nullable', 'string', 'max:95'],
            'og_description' => ['nullable', 'string', 'max:200'],
            'og_image_id' => ['nullable', 'integer', Rule::exists('media', 'id')->where(fn ($query) => $query->where('mime_type', 'like', 'image/%')->whereNotNull('alt_text')->where('alt_text', '!=', ''))],
            'published_at' => ['nullable', 'date'],
            'document_ids' => ['sometimes', 'array', 'max:20'],
            'document_ids.*' => ['integer', 'distinct', 'exists:documents,id'],
        ];
    }
}
