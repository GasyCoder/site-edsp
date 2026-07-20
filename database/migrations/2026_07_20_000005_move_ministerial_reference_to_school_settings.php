<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            ['ministerial_reference_label', 'Référence ministérielle'],
            ['ministerial_reference', 'Arrêté n°8008/2014-MESupRES du 29 janvier 2014'],
            ['ministerial_reference_label_en', 'Ministerial accreditation'],
            ['ministerial_reference_en', 'Order No. 8008/2014-MESupRES of 29 January 2014'],
        ] as [$key, $value]) {
            DB::table('settings')->updateOrInsert(['key' => $key], [
                'value' => $value,
                'type' => 'string',
                'group' => 'legal',
                'is_public' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]);
        }

        $pageId = DB::table('pages')->where('slug', 'presentation')->value('id');
        if (! $pageId) {
            Cache::forget('settings.public');

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

        Cache::forget('settings.public');
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'ministerial_reference_label',
            'ministerial_reference',
            'ministerial_reference_label_en',
            'ministerial_reference_en',
        ])->delete();

        Cache::forget('settings.public');
    }

    /** @return array<string, mixed> */
    private function decode(?string $value): array
    {
        $decoded = json_decode($value ?: '{}', true);

        return is_array($decoded) ? $decoded : [];
    }
};
