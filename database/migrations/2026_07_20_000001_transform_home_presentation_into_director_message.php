<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $homeId = DB::table('pages')->where('slug', 'accueil')->value('id');

        if (! $homeId) {
            return;
        }

        DB::table('page_sections')
            ->where('page_id', $homeId)
            ->where('section_key', 'presentation')
            ->get(['id', 'settings', 'translations'])
            ->each(function (object $section): void {
                $settings = $this->decode($section->settings);
                $translations = $this->decode($section->translations);
                $translations['en'] = is_array($translations['en'] ?? null) ? $translations['en'] : [];
                $translations['en'] = [
                    ...$translations['en'],
                    'title' => 'Pr. Liva Jackson Raharinaivo',
                    'subtitle' => "Director's message",
                    'content' => 'Dear students, EDSP welcomes you to an environment where academic excellence, critical thinking and a sense of responsibility guide every programme. Our ambition is to educate legal professionals and political science specialists who are ready to serve society and support Madagascar’s transformation.',
                    'button_text' => "Read the Director's message",
                    'settings' => [
                        ...(is_array($translations['en']['settings'] ?? null) ? $translations['en']['settings'] : []),
                        'director_position' => 'Director of EDSP',
                        'director_signature' => 'Welcome to EDSP.',
                        'alt_text' => 'Portrait of the Director of EDSP',
                    ],
                ];

                DB::table('page_sections')->where('id', $section->id)->update([
                    'title' => 'Pr. Liva Jackson Raharinaivo',
                    'subtitle' => 'Mot du directeur',
                    'content' => 'Chères étudiantes, chers étudiants, l’EDSP vous accueille dans un environnement où l’exigence académique, l’esprit critique et le sens des responsabilités guident chaque formation. Notre ambition est de former des juristes et des spécialistes de la science politique capables de servir la société et d’accompagner les transformations de Madagascar.',
                    'button_text' => 'Lire le mot du directeur',
                    'button_url' => '/presentation',
                    'settings' => json_encode([
                        ...$settings,
                        'director_position' => 'Directeur de l’EDSP',
                        'director_signature' => 'Bienvenue à toutes et à tous à l’EDSP.',
                        'alt_text' => 'Portrait du directeur de l’EDSP',
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'translations' => json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        $homeId = DB::table('pages')->where('slug', 'accueil')->value('id');

        if (! $homeId) {
            return;
        }

        DB::table('page_sections')
            ->where('page_id', $homeId)
            ->where('section_key', 'presentation')
            ->get(['id', 'settings', 'translations'])
            ->each(function (object $section): void {
                $settings = $this->decode($section->settings);
                unset($settings['director_position'], $settings['director_signature']);
                $settings['alt_text'] = 'Photo de l’établissement ou des étudiants';

                $translations = $this->decode($section->translations);
                $translations['en'] = is_array($translations['en'] ?? null) ? $translations['en'] : [];
                $translations['en'] = [
                    ...$translations['en'],
                    'title' => 'Welcome to EDSP',
                    'subtitle' => 'The School',
                    'content' => 'EDSP educates students to understand, analyse and support Madagascar’s legal, administrative, social and political transformations.',
                    'button_text' => 'Learn more',
                ];
                if (is_array($translations['en']['settings'] ?? null)) {
                    unset(
                        $translations['en']['settings']['director_position'],
                        $translations['en']['settings']['director_signature'],
                    );
                    $translations['en']['settings']['alt_text'] = 'Photo of the School or its students';
                }

                DB::table('page_sections')->where('id', $section->id)->update([
                    'title' => 'Bienvenue à l’EDSP',
                    'subtitle' => 'L’établissement',
                    'content' => 'L’EDSP forme des étudiants capables de comprendre, d’analyser et d’accompagner les transformations juridiques, administratives, sociales et politiques de Madagascar.',
                    'button_text' => 'En savoir plus',
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
