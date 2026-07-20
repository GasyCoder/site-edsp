<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('pages')->where('slug', 'presentation')->first(['id', 'translations']);

        if (! $page) {
            return;
        }

        $pageTranslations = $this->decode($page->translations);
        $pageTranslations['en'] = [
            ...(is_array($pageTranslations['en'] ?? null) ? $pageTranslations['en'] : []),
            'title' => "Director's message",
            'meta_title' => "Director's message — EDSP",
            'meta_description' => "Discover the Director of the School of Law and Political Science's vision and message to students and prospective applicants.",
        ];

        DB::table('pages')->where('id', $page->id)->update([
            'title' => 'Le mot du directeur',
            'meta_title' => 'Le mot du directeur — EDSP',
            'meta_description' => 'Découvrez la vision et le message du directeur de l’École de Droit et Science Politique aux étudiants et futurs candidats.',
            'translations' => json_encode($pageTranslations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'updated_at' => now(),
        ]);

        DB::table('page_sections')
            ->where('page_id', $page->id)
            ->where('section_key', 'main')
            ->get(['id', 'settings', 'translations'])
            ->each(function (object $section): void {
                $settings = $this->decode($section->settings);
                $translations = $this->decode($section->translations);
                $translations['en'] = [
                    ...(is_array($translations['en'] ?? null) ? $translations['en'] : []),
                    'title' => 'Pr. Liva Jackson Raharinaivo',
                    'subtitle' => "Director's message",
                    'content' => '<p>Dear students,</p><p>It is a genuine pleasure to welcome you to the School of Law and Political Science. Our School places academic excellence, critical thinking and a sense of responsibility at the heart of every programme.</p><p>Our ambition is to educate legal professionals and political science specialists who can understand the transformations affecting our society, inform public decision-making and contribute with integrity to Madagascar’s development.</p><p>At EDSP, you will find a committed teaching team, structured Bachelor’s and Master’s pathways, and an environment that supports achievement, professional development and civic engagement.</p><p>I invite you to build your academic future fully within our School.</p>',
                    'settings' => [
                        ...(is_array($translations['en']['settings'] ?? null) ? $translations['en']['settings'] : []),
                        'director_position' => 'Director of EDSP',
                        'director_signature' => 'With my very best wishes,',
                        'alt_text' => 'Portrait of the Director of EDSP',
                    ],
                ];

                DB::table('page_sections')->where('id', $section->id)->update([
                    'section_type' => 'director-message',
                    'title' => 'Pr. Liva Jackson Raharinaivo',
                    'subtitle' => 'Mot du directeur',
                    'content' => '<p>Chères étudiantes, chers étudiants,</p><p>C’est avec un réel plaisir que je vous souhaite la bienvenue à l’École de Droit et Science Politique. Notre établissement place l’exigence académique, l’esprit critique et le sens des responsabilités au cœur de chaque formation.</p><p>Notre ambition est de former des juristes et des spécialistes de la science politique capables de comprendre les transformations de notre société, d’éclairer la décision publique et de contribuer avec intégrité au développement de Madagascar.</p><p>À l’EDSP, vous trouverez une équipe pédagogique engagée, des parcours structurés de la Licence au Master et un environnement favorable à la réussite, à l’ouverture professionnelle et à l’engagement citoyen.</p><p>Je vous invite à construire pleinement votre projet universitaire au sein de notre école.</p>',
                    'button_text' => null,
                    'button_url' => null,
                    'settings' => json_encode([
                        ...$settings,
                        'background' => 'white',
                        'alignment' => 'left',
                        'container' => 'wide',
                        'director_position' => 'Directeur de l’EDSP',
                        'director_signature' => 'Avec tous mes encouragements,',
                        'alt_text' => 'Portrait du directeur de l’EDSP',
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'translations' => json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        $page = DB::table('pages')->where('slug', 'presentation')->first(['id']);

        if (! $page) {
            return;
        }

        DB::table('pages')->where('id', $page->id)->update([
            'title' => 'Présentation de l’EDSP',
            'meta_title' => 'Présentation de l’EDSP — EDSP',
            'meta_description' => 'L’EDSP accompagne la formation de juristes et de spécialistes de la science politique.',
            'updated_at' => now(),
        ]);

        DB::table('page_sections')
            ->where('page_id', $page->id)
            ->where('section_key', 'main')
            ->update([
                'section_type' => 'rich-content',
                'title' => 'Présentation de l’EDSP',
                'subtitle' => null,
                'content' => 'L’EDSP accompagne la formation de juristes et de spécialistes de la science politique au sein de l’Université de Mahajanga.',
                'updated_at' => now(),
            ]);
    }

    /** @return array<string, mixed> */
    private function decode(?string $value): array
    {
        $decoded = json_decode($value ?: '{}', true);

        return is_array($decoded) ? $decoded : [];
    }
};
