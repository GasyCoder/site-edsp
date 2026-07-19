<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<string, list<string>> */
    private array $contentColumns = [
        'pages' => ['title', 'slug', 'meta_title', 'meta_description', 'meta_keywords', 'canonical_url', 'og_title', 'og_description'],
        'page_sections' => ['title', 'subtitle', 'content', 'button_text', 'button_url', 'settings'],
        'news' => ['title', 'slug', 'excerpt', 'content', 'meta_title', 'meta_description', 'canonical_url', 'og_title', 'og_description'],
        'programs' => ['title', 'description', 'objectives', 'admission_requirements', 'skills', 'careers', 'curriculum', 'meta_title', 'meta_description'],
        'admission_campaigns' => ['title', 'instructions', 'required_documents'],
        'settings' => ['value'],
    ];

    public function up(): void
    {
        $this->replaceContent([
            'Se préinscrire' => 'S’inscrire',
            'se préinscrire' => 's’inscrire',
            'de préinscription' => 'd’inscription',
            'la préinscription' => 'l’inscription',
            'La préinscription' => 'L’inscription',
            'Préinscriptions' => 'Inscriptions',
            'préinscriptions' => 'inscriptions',
            'Préinscription' => 'Inscription',
            'préinscription' => 'inscription',
            'preinscriptions' => 'inscriptions',
            'preinscription' => 'inscription',
        ]);

        if (Schema::hasTable('redirects') && Schema::hasTable('news') && DB::table('news')->where('slug', 'ouverture-des-inscriptions')->exists()) {
            DB::table('redirects')->updateOrInsert(
                ['source_path' => '/actualites/ouverture-des-preinscriptions'],
                [
                    'destination_url' => '/actualites/ouverture-des-inscriptions',
                    'status_code' => 301,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('redirects')) {
            DB::table('redirects')
                ->where('source_path', '/actualites/ouverture-des-preinscriptions')
                ->where('destination_url', '/actualites/ouverture-des-inscriptions')
                ->delete();
        }

        $this->replaceContent([
            'S’inscrire' => 'Se préinscrire',
            's’inscrire' => 'se préinscrire',
            'd’inscription' => 'de préinscription',
            'l’inscription' => 'la préinscription',
            'L’inscription' => 'La préinscription',
            'Inscriptions' => 'Préinscriptions',
            'inscriptions' => 'préinscriptions',
            'Inscription' => 'Préinscription',
            'inscription' => 'préinscription',
        ]);
    }

    /** @param  array<string, string>  $replacements */
    private function replaceContent(array $replacements): void
    {
        foreach ($this->contentColumns as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $columns = array_values(array_filter(
                $columns,
                fn (string $column): bool => Schema::hasColumn($table, $column),
            ));

            if ($columns === []) {
                continue;
            }

            DB::table($table)
                ->select(['id', ...$columns])
                ->orderBy('id')
                ->chunkById(100, function ($rows) use ($table, $columns, $replacements): void {
                    foreach ($rows as $row) {
                        $changes = [];

                        foreach ($columns as $column) {
                            $current = $row->{$column};

                            if (! is_string($current) || $current === '') {
                                continue;
                            }

                            $updated = $this->replaceValue($current, $replacements);

                            if ($updated !== $current) {
                                $changes[$column] = $updated;
                            }
                        }

                        if ($changes !== []) {
                            DB::table($table)->where('id', $row->id)->update($changes);
                        }
                    }
                });
        }
    }

    /** @param  array<string, string>  $replacements */
    private function replaceValue(string $value, array $replacements): string
    {
        $decoded = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return (string) json_encode(
                $this->replaceArray($decoded, $replacements),
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            );
        }

        return strtr($value, $replacements);
    }

    /**
     * @param  array<array-key, mixed>  $values
     * @param  array<string, string>  $replacements
     * @return array<array-key, mixed>
     */
    private function replaceArray(array $values, array $replacements): array
    {
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $values[$key] = $this->replaceArray($value, $replacements);
            } elseif (is_string($value)) {
                $values[$key] = strtr($value, $replacements);
            }
        }

        return $values;
    }
};
