<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInstitutionalReferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && (
            $user->can('edit settings')
            || $user->hasAnyRole(['superadmin', 'manager'])
        );
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'ministerial_reference_label' => ['required', 'string', 'max:180'],
            'ministerial_reference' => ['required', 'string', 'max:500'],
            'accreditation_reference_label' => ['required', 'string', 'max:180'],
            'accreditation_reference' => ['required', 'string', 'max:1000'],
        ];
    }
}
