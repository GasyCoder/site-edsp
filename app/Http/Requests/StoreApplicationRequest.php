<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

class StoreApplicationRequest extends FormRequest
{
    /** @var array<string, list<string>> */
    private const DOCUMENT_MIME_TYPES = [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'webp' => ['image/webp'],
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'admission_campaign_id' => ['required', 'integer', 'exists:admission_campaigns,id'],
            'program_id' => ['required', 'integer', 'exists:programs,id'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['required', 'string', 'max:40'],
            'birth_date' => ['required', 'date', 'before:today'],
            'address' => ['required', 'string', 'max:500'],
            'academic_background' => ['required', 'string', 'max:3000'],
            'privacy_accepted' => ['accepted'],
            'documents' => ['sometimes', 'array', 'max:10'],
            'documents.*' => ['file', 'max:5120', 'extensions:jpg,jpeg,png,webp,pdf,doc,docx', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx'],
            'website' => ['nullable', 'max:0'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            foreach ($this->file('documents', []) as $key => $file) {
                if (! $file instanceof UploadedFile || ! $file->isValid()) {
                    continue;
                }

                $extension = Str::lower($file->getClientOriginalExtension());
                $mimeType = (string) $file->getMimeType();

                if (! isset(self::DOCUMENT_MIME_TYPES[$extension]) || ! in_array($mimeType, self::DOCUMENT_MIME_TYPES[$extension], true)) {
                    $validator->errors()->add("documents.{$key}", 'Le type réel du document ne correspond pas à son extension.');
                }
            }
        }];
    }
}
