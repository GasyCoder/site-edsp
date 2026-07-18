<?php

namespace App\Services;

use Mews\Purifier\Facades\Purifier;

final class RichTextSanitizer
{
    private const ALLOWED_HTML = 'p,br,h2,h3,h4,strong,b,em,i,u,s,blockquote,ul,ol,li,a[href|title|rel],table,thead,tbody,tr,th,td,hr,sub,sup';

    public function clean(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return $html;
        }

        return Purifier::clean($html, [
            'HTML.Allowed' => self::ALLOWED_HTML,
            'URI.DisableExternalResources' => true,
            'URI.DisableResources' => true,
        ]);
    }

    /** @param array<string, mixed> $values @param list<string> $keys */
    public function cleanFields(array $values, array $keys): array
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $values) && is_string($values[$key])) {
                $values[$key] = $this->clean($values[$key]);
            }
        }

        return $values;
    }
}
