<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['first_name' => ['nullable', 'string', 'max:100'], 'last_name' => ['required', 'string', 'max:100'], 'email' => ['required', 'email:rfc', 'max:255'], 'phone' => ['nullable', 'string', 'max:40'], 'organization' => ['nullable', 'string', 'max:180'], 'subject' => ['required', 'string', 'max:180'], 'message' => ['required', 'string', 'min:10', 'max:5000'], 'consent' => ['accepted'], 'website' => ['nullable', 'max:0']];
    }
}
