<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('page_sections')
            ->where('section_key', 'programs')
            ->get(['id', 'settings', 'translations'])
            ->each(function (object $section): void {
                $settings = $this->decode($section->settings);
                $translations = $this->decode($section->translations);

                if (in_array($settings['footer_label'] ?? null, ['Diplômes et niveaux proposés :', 'Diplômes et niveaux proposés'], true)) {
                    $settings['footer_label'] = 'Diplômes délivrés :';
                }

                $englishSettings = $translations['en']['settings'] ?? [];
                if (is_array($englishSettings) && in_array($englishSettings['footer_label'] ?? null, ['Qualifications and levels:', 'Qualifications and levels'], true)) {
                    $translations['en']['settings']['footer_label'] = 'Degrees awarded:';
                }

                DB::table('page_sections')->where('id', $section->id)->update([
                    'settings' => json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'translations' => json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        // The corrected labels remain valid if this migration is rolled back.
    }

    /** @return array<string, mixed> */
    private function decode(?string $value): array
    {
        $decoded = json_decode($value ?: '{}', true);

        return is_array($decoded) ? $decoded : [];
    }
};
