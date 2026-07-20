<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('page_sections')
            ->where('section_key', 'hero')
            ->get(['id', 'settings'])
            ->each(function (object $section): void {
                $settings = json_decode($section->settings ?: '{}', true);
                $settings = is_array($settings) ? $settings : [];

                if (($settings['title_highlight_1_color'] ?? null) === 'red') {
                    $settings['title_highlight_1_color'] = 'institutional';
                }
                if (($settings['title_highlight_2_color'] ?? null) === 'red') {
                    $settings['title_highlight_2_color'] = 'institutional';
                }

                DB::table('page_sections')->where('id', $section->id)->update([
                    'settings' => json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        // The accessible blue fill is intentionally preserved.
    }
};
