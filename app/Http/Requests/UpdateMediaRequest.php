<?php

namespace App\Http\Requests;

use App\Models\Media;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('edit media') ?? false;
    }

    public function rules(): array
    {
        return ['alt_text' => ['nullable', 'string', 'max:255'], 'caption' => ['nullable', 'string', 'max:1000']];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $media = $this->route('media');

            if ($media instanceof Media && str_starts_with((string) $media->mime_type, 'image/') && blank($this->input('alt_text'))) {
                $validator->errors()->add('alt_text', 'Un texte alternatif est requis pour une image.');
            }
        }];
    }
}
