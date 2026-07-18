<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UploadMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('upload media') ?? false;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $file = $this->file('file');
            if ($file?->isValid() && str_starts_with((string) $file->getMimeType(), 'image/') && blank($this->input('alt_text'))) {
                $validator->errors()->add('alt_text', 'Un texte alternatif est requis pour une image.');
            }

            if (! $file?->isValid() || ! str_starts_with((string) $file->getMimeType(), 'image/')) {
                return;
            }

            $dimensions = @getimagesize($file->getRealPath());

            if ($dimensions === false || $dimensions[0] > 12000 || $dimensions[1] > 12000 || ($dimensions[0] * $dimensions[1]) > 40_000_000) {
                $validator->errors()->add('file', 'Les dimensions de cette image sont invalides ou excessives.');
            }
        }];
    }
}
