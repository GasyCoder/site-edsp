<?php

namespace App\Services;

use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsletterSubscriberImporter
{
    /** @return array{created: int, updated: int, invalid: int} */
    public function fromStoredFile(string $disk, string $path, bool $activate = true): array
    {
        $contents = Storage::disk($disk)->get($path);

        return $this->fromText($contents, $activate);
    }

    /** @return array{created: int, updated: int, invalid: int} */
    public function fromText(string $contents, bool $activate = true): array
    {
        $result = ['created' => 0, 'updated' => 0, 'invalid' => 0];
        $lines = preg_split('/\R/u', trim($contents)) ?: [];
        $header = null;

        foreach ($lines as $index => $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $delimiter = substr_count($line, ';') >= substr_count($line, ',') ? ';' : ',';
            $columns = array_map('trim', str_getcsv($line, $delimiter));

            if ($index === 0 && collect($columns)->contains(fn (string $value): bool => in_array(Str::lower($value), ['email', 'e-mail', 'adresse email', 'adresse e-mail'], true))) {
                $header = collect($columns)->map(fn (string $value): string => Str::lower($value))->all();

                continue;
            }

            $emailIndex = $header === null ? 0 : ($this->findHeaderIndex($header, ['email', 'e-mail', 'adresse email', 'adresse e-mail']) ?? 0);
            $nameIndex = $header === null ? 1 : $this->findHeaderIndex($header, ['name', 'nom', 'nom complet']);
            $email = Str::lower(trim((string) ($columns[$emailIndex] ?? '')));
            $name = $nameIndex === null ? null : (trim((string) ($columns[$nameIndex] ?? '')) ?: null);

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $result['invalid']++;

                continue;
            }

            $subscriber = NewsletterSubscriber::query()->firstOrNew(['email' => $email]);
            $exists = $subscriber->exists;
            $subscriber->forceFill([
                'name' => $name ?? $subscriber->name,
                'source' => $exists ? $subscriber->source : 'import',
                'imported_at' => now(),
                'verified_at' => $activate ? ($subscriber->verified_at ?? now()) : $subscriber->verified_at,
                'unsubscribed_at' => $activate ? null : $subscriber->unsubscribed_at,
            ])->save();

            $result[$exists ? 'updated' : 'created']++;
        }

        return $result;
    }

    /** @param list<string> $headers */
    private function findHeaderIndex(array $headers, array $names): ?int
    {
        foreach ($names as $name) {
            $index = array_search($name, $headers, true);

            if ($index !== false) {
                return $index;
            }
        }

        return null;
    }
}
