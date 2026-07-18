<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PublicSlug implements ValidationRule
{
    /** @var list<string> */
    private const RESERVED = [
        'admin',
        'administration',
        'api',
        'actualites',
        'build',
        'documents',
        'edition',
        'formations',
        'login',
        'logout',
        'preinscription',
        'robots',
        'sitemap',
        'storage',
        'up',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $slug = mb_strtolower((string) $value);

        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            $fail('L’identifiant URL ne peut contenir que des lettres minuscules, des chiffres et des tirets simples.');

            return;
        }

        if (in_array($slug, self::RESERVED, true)) {
            $fail('Cet identifiant URL est réservé par le site.');
        }
    }
}
