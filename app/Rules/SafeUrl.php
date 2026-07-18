<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SafeUrl implements ValidationRule
{
    public function __construct(private readonly bool $allowRelative = true) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            return;
        }

        if ($this->allowRelative && str_starts_with($value, '/') && ! str_starts_with($value, '//')) {
            return;
        }

        $scheme = parse_url($value, PHP_URL_SCHEME);
        if (! filter_var($value, FILTER_VALIDATE_URL) || ! in_array(strtolower((string) $scheme), ['http', 'https'], true)) {
            $fail('Le champ :attribute doit être une URL HTTP(S) ou un chemin interne valide.');
        }
    }
}
