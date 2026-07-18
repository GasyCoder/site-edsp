<?php

namespace App\Http\Requests;

use App\Enums\ContentStatus;
use App\Rules\PublicSlug;
use App\Rules\SafeUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StorePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create pages') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('slug') && $this->filled('title')) {
            $this->merge(['slug' => Str::slug($this->string('title')->toString())]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['required', new PublicSlug, 'max:180', 'unique:pages,slug'],
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'template' => ['required', 'string', 'max:80'],
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
        ];
    }
}
