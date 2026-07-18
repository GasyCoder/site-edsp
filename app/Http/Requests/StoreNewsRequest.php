<?php

namespace App\Http\Requests;

use App\Enums\ContentStatus;
use App\Rules\PublicSlug;
use App\Rules\SafeUrl;
use App\Services\RichTextSanitizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create news') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $values = app(RichTextSanitizer::class)->cleanFields($this->all(), ['content']);
        if (blank($values['slug'] ?? null) && filled($values['title'] ?? null)) {
            $values['slug'] = Str::slug((string) $values['title']);
        }
        $this->replace($values);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['required', new PublicSlug, 'max:180', 'unique:news,slug'],
            'excerpt' => ['required', 'string', 'max:500'],
            'content' => ['required', 'string', 'max:100000'],
            'featured_image_id' => ['nullable', 'integer', Rule::exists('media', 'id')->where(fn ($query) => $query->where('mime_type', 'like', 'image/%')->whereNotNull('alt_text')->where('alt_text', '!=', ''))],
            'category_id' => ['nullable', 'integer', 'exists:news_categories,id'],
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'is_featured' => ['required', 'boolean'],
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
            'gallery_ids' => ['sometimes', 'array', 'max:10'],
            'gallery_ids.*' => ['integer', 'distinct', 'exists:galleries,id'],
        ];
    }
}
