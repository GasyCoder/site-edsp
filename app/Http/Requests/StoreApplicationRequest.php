<?php

namespace App\Http\Requests;

use App\Models\ParcoursLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
        $currentYear = (int) now()->year;

        return [
            'admission_campaign_id' => ['required', 'integer', 'exists:admission_campaigns,id'],
            'program_id' => ['required', 'integer', 'exists:programs,id'],
            'civility' => ['required', Rule::in(['monsieur', 'madame', 'mademoiselle'])],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'gender' => ['required', Rule::in(['masculin', 'feminin'])],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['required', 'string', 'max:40'],
            'birth_date' => ['required', 'date', 'before:today'],
            'birth_place' => ['required', 'string', 'max:255'],
            'nationality' => ['required', 'string', 'max:100'],
            'national_id' => ['nullable', 'string', 'max:80'],
            'address' => ['required', 'string', 'max:500'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'parent_phone' => ['nullable', 'string', 'max:40'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_relationship' => ['nullable', 'string', 'max:100'],
            'guardian_phone' => ['nullable', 'string', 'max:40'],
            'academic_level_id' => ['required', 'integer', 'exists:levels,id'],
            'mention_id' => ['required', 'integer', 'exists:mentions,id'],
            'parcours_id' => ['required', 'integer', 'exists:parcours,id'],
            'last_diploma' => ['required', 'string', 'max:255'],
            'graduation_year' => ['required', 'integer', 'min:1950', 'max:'.$currentYear],
            'previous_institution' => ['required', 'string', 'max:255'],
            'academic_background' => ['nullable', 'string', 'max:3000'],
            'privacy_accepted' => ['accepted'],
            'documents' => ['sometimes', 'array', 'max:10'],
            'documents.*' => ['file', 'max:5120', 'extensions:jpg,jpeg,png,webp,pdf,doc,docx', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx'],
            'website' => ['nullable', 'max:0'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! $this->filled('parent_phone') && ! $this->filled('guardian_phone')) {
                $validator->errors()->add('parent_phone', 'Indiquez au moins le téléphone d’un parent ou du répondant.');
            }

            if ($this->filled(['academic_level_id', 'mention_id', 'parcours_id'])) {
                $validAcademicChoice = ParcoursLevel::query()
                    ->where('level_id', $this->integer('academic_level_id'))
                    ->where('parcours_id', $this->integer('parcours_id'))
                    ->where('is_active', true)
                    ->whereHas('parcours', fn ($query) => $query->where('mention_id', $this->integer('mention_id')))
                    ->exists();

                if (! $validAcademicChoice) {
                    $validator->errors()->add('parcours_id', 'Le parcours sélectionné n’est pas proposé pour ce niveau et cette mention.');
                }
            }

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

    protected function prepareForValidation(): void
    {
        $textFields = [
            'first_name', 'last_name', 'birth_place', 'nationality', 'national_id', 'address',
            'father_name', 'mother_name', 'parent_phone', 'guardian_name', 'guardian_relationship',
            'guardian_phone', 'last_diploma', 'previous_institution', 'academic_background',
        ];
        $normalized = ['email' => mb_strtolower(trim((string) $this->input('email')))];

        foreach ($textFields as $field) {
            if ($this->has($field)) {
                $normalized[$field] = trim((string) $this->input($field));
            }
        }

        $this->merge($normalized);
    }

    public function messages(): array
    {
        return [
            'required' => 'Le champ :attribute est obligatoire.',
            'email.email' => 'Saisissez une adresse e-mail valide.',
            'birth_date.before' => 'La date de naissance doit être antérieure à aujourd’hui.',
            'graduation_year.max' => 'L’année d’obtention ne peut pas être dans le futur.',
            'privacy_accepted.accepted' => 'Vous devez accepter la politique de confidentialité.',
        ];
    }

    public function attributes(): array
    {
        return [
            'civility' => 'civilité',
            'first_name' => 'prénom(s)',
            'last_name' => 'nom',
            'gender' => 'genre',
            'birth_date' => 'date de naissance',
            'birth_place' => 'lieu de naissance',
            'nationality' => 'nationalité',
            'email' => 'adresse e-mail',
            'phone' => 'téléphone',
            'address' => 'adresse',
            'academic_level_id' => 'niveau',
            'mention_id' => 'mention',
            'parcours_id' => 'parcours',
            'last_diploma' => 'dernier diplôme obtenu',
            'graduation_year' => 'année d’obtention',
            'previous_institution' => 'établissement précédent',
        ];
    }
}
