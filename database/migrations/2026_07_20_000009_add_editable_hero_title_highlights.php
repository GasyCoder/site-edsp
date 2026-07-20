<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('page_sections')
            ->where('section_key', 'hero')
            ->get(['id', 'settings', 'translations'])
            ->each(function (object $section): void {
                $settings = $this->decode($section->settings);
                $translations = $this->decode($section->translations);

                $settings['title_highlight_1'] ??= 'droit';
                $settings['title_highlight_1_color'] ??= 'green';
                $settings['title_highlight_2'] ??= 'science politique';
                $settings['title_highlight_2_color'] ??= 'institutional';

                if (in_array($settings['kicker_text'] ?? null, [null, 'Deux parcours :'], true)) {
                    $settings['kicker_text'] = 'Deux mentions :';
                }
                if (in_array($settings['rotating_item_1'] ?? null, [null, 'Droit privé'], true)) {
                    $settings['rotating_item_1'] = 'Droit';
                }
                if (in_array($settings['rotating_item_2'] ?? null, [null, 'Science politique'], true)) {
                    $settings['rotating_item_2'] = 'Sciences Politiques';
                }

                $translations['en'] = is_array($translations['en'] ?? null) ? $translations['en'] : [];
                $translations['en']['settings'] = [
                    ...(is_array($translations['en']['settings'] ?? null) ? $translations['en']['settings'] : []),
                    'title_highlight_1' => 'law',
                    'title_highlight_2' => 'political science',
                    'kicker_text' => 'Two subject areas:',
                    'rotating_item_1' => 'Law',
                    'rotating_item_2' => 'Political Science',
                ];

                DB::table('page_sections')->where('id', $section->id)->update([
                    'settings' => json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'translations' => json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        // Highlight settings are harmless and intentionally preserved on rollback.
    }

    /** @return array<string, mixed> */
    private function decode(?string $value): array
    {
        $decoded = json_decode($value ?: '{}', true);

        return is_array($decoded) ? $decoded : [];
    }
};
