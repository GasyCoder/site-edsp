<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNewsletterSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->input('email'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'max:255'],
            'website' => ['nullable', 'max:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Saisissez votre adresse e-mail.',
            'email.email' => 'Saisissez une adresse e-mail valide.',
            'email.max' => 'L’adresse e-mail est trop longue.',
            'website.max' => 'Votre inscription n’a pas pu être envoyée.',
        ];
    }
}
