<?php

namespace App\Models;

use App\Rules\SafeUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Redirect extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'status_code' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saving(function (Redirect $redirect): void {
            Validator::make($redirect->only(['source_path', 'destination_url']), [
                'source_path' => ['required', 'regex:#^/(?!/)[^\s]*$#'],
                'destination_url' => ['required', 'different:source_path', new SafeUrl],
            ])->validate();

            $visited = [$redirect->source_path];
            $next = self::internalPath($redirect->destination_url);

            for ($depth = 0; $next !== null && $depth < 25; $depth++) {
                if (in_array($next, $visited, true)) {
                    throw ValidationException::withMessages([
                        'destination_url' => 'Cette destination créerait une boucle de redirection.',
                    ]);
                }

                $visited[] = $next;
                $following = self::query()
                    ->where('source_path', $next)
                    ->where('is_active', true)
                    ->when($redirect->exists, fn ($query) => $query->whereKeyNot($redirect->getKey()))
                    ->value('destination_url');
                $next = filled($following) ? self::internalPath((string) $following) : null;
            }
        });
    }

    private static function internalPath(string $url): ?string
    {
        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return '/'.ltrim((string) parse_url($url, PHP_URL_PATH), '/');
        }

        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        $host = parse_url($url, PHP_URL_HOST);

        if ($host === null || $appHost === null || strcasecmp((string) $host, (string) $appHost) !== 0) {
            return null;
        }

        return '/'.ltrim((string) parse_url($url, PHP_URL_PATH), '/');
    }
}
