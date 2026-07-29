<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'pages' => ['meta_title', 'og_title'],
            'news' => ['meta_title', 'og_title'],
            'programs' => ['meta_title', 'og_title'],
        ] as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $availableColumns = array_values(array_filter(
                $columns,
                fn (string $column): bool => Schema::hasColumn($table, $column),
            ));

            if ($availableColumns === []) {
                continue;
            }

            DB::table($table)
                ->select(['id', ...$availableColumns])
                ->orderBy('id')
                ->get()
                ->each(function (object $record) use ($availableColumns, $table): void {
                    $changes = [];

                    foreach ($availableColumns as $column) {
                        $current = $record->{$column};
                        $normalized = $this->normalize($current, ' | ');

                        if ($normalized !== $current) {
                            $changes[$column] = $normalized;
                        }
                    }

                    if ($changes !== []) {
                        DB::table($table)->where('id', $record->id)->update($changes);
                    }
                });
        }

        if (! Schema::hasTable('settings')) {
            return;
        }

        DB::table('settings')
            ->whereIn('key', [
                'seo_default_title',
                'default_meta_title',
                'default_meta_title_en',
            ])
            ->get(['id', 'value'])
            ->each(function (object $setting): void {
                DB::table('settings')->where('id', $setting->id)->update([
                    'value' => $this->normalize($setting->value, ' | '),
                ]);
            });

        DB::table('settings')
            ->whereIn('key', ['accreditation_reference', 'accreditation_reference_en'])
            ->get(['id', 'value'])
            ->each(function (object $setting): void {
                DB::table('settings')->where('id', $setting->id)->update([
                    'value' => $this->normalize($setting->value, ', '),
                ]);
            });

        Cache::forget('settings.public');
    }

    public function down(): void
    {
        // La ponctuation éditoriale normalisée ne doit pas être restaurée.
    }

    private function normalize(?string $value, string $separator): ?string
    {
        if ($value === null) {
            return null;
        }

        return trim((string) preg_replace('/\s*[—–]\s*/u', $separator, $value));
    }
};
