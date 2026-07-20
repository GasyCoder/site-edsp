<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $homePageId = DB::table('pages')->where('slug', 'accueil')->value('id');
        $presentationPageId = DB::table('pages')->where('slug', 'presentation')->value('id');

        if (! $homePageId || ! $presentationPageId) {
            return;
        }

        $homeSection = DB::table('page_sections')
            ->where('page_id', $homePageId)
            ->where('section_key', 'presentation')
            ->first(['title', 'image_id']);
        $officialSection = DB::table('page_sections')
            ->where('page_id', $presentationPageId)
            ->where('section_key', 'main')
            ->first(['id', 'image_id', 'translations']);

        if (! $officialSection) {
            return;
        }

        $officialName = is_string($homeSection?->title) && trim($homeSection->title) !== ''
            ? trim($homeSection->title)
            : 'Pr. Liva Jackson Raharinaivo';
        $translations = $this->decode($officialSection->translations);
        $translations['en'] = is_array($translations['en'] ?? null) ? $translations['en'] : [];
        $translations['en']['title'] = $officialName;

        DB::table('page_sections')->where('id', $officialSection->id)->update([
            'title' => $officialName,
            'image_id' => $officialSection->image_id ?: $homeSection?->image_id,
            'translations' => json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // The previous duplicated values are intentionally not restored.
    }

    /** @return array<string, mixed> */
    private function decode(?string $value): array
    {
        $decoded = json_decode($value ?: '{}', true);

        return is_array($decoded) ? $decoded : [];
    }
};
