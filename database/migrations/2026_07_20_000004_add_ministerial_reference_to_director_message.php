<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $pageId = DB::table('pages')->where('slug', 'presentation')->value('id');

        if (! $pageId) {
            return;
        }

        DB::table('page_sections')
            ->where('page_id', $pageId)
            ->where('section_key', 'main')
            ->get(['id', 'settings', 'translations'])
            ->each(function (object $section): void {
                $settings = $this->decode($section->settings);
                $translations = $this->decode($section->translations);
                $translations['en'] = is_array($translations['en'] ?? null) ? $translations['en'] : [];
                $translations['en']['settings'] = is_array($translations['en']['settings'] ?? null)
                    ? $translations['en']['settings']
                    : [];
                $translations['en']['settings']['legal_reference_label'] = 'Ministerial accreditation';
                $translations['en']['settings']['legal_reference'] = 'Order No. 8008/2014-MESupRES of 29 January 2014';

                DB::table('page_sections')->where('id', $section->id)->update([
                    'settings' => json_encode([
                        ...$settings,
                        'legal_reference_label' => 'Référence ministérielle',
                        'legal_reference' => 'Arrêté n°8008/2014-MESupRES du 29 janvier 2014',
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'translations' => json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        $pageId = DB::table('pages')->where('slug', 'presentation')->value('id');

        if (! $pageId) {
            return;
        }

        DB::table('page_sections')
            ->where('page_id', $pageId)
            ->where('section_key', 'main')
            ->get(['id', 'settings', 'translations'])
            ->each(function (object $section): void {
                $settings = $this->decode($section->settings);
                $translations = $this->decode($section->translations);
                unset($settings['legal_reference_label'], $settings['legal_reference']);
                unset(
                    $translations['en']['settings']['legal_reference_label'],
                    $translations['en']['settings']['legal_reference'],
                );

                DB::table('page_sections')->where('id', $section->id)->update([
                    'settings' => json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'translations' => json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'updated_at' => now(),
                ]);
            });
    }

    /** @return array<string, mixed> */
    private function decode(?string $value): array
    {
        $decoded = json_decode($value ?: '{}', true);

        return is_array($decoded) ? $decoded : [];
    }
};
