<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            'hero' => [
                'secondary_button_text' => 'Apply now',
                'kicker_text' => 'Two pathways:',
                'degree_text' => 'Bachelor’s · Master’s',
                'visual_eyebrow' => 'Choose your programme',
                'visual_title' => 'A degree grounded in Madagascar’s legal and public realities.',
                'visual_footer' => 'Private Law · Political Science',
                'rotating_item_1' => 'Private Law',
                'rotating_item_2' => 'Political Science',
                'alt_text' => 'EDSP campus and student life',
            ],
            'presentation' => [
                'feature_1_title' => 'Academic excellence',
                'feature_1_description' => 'Rigorous teaching grounded in Malagasy law and open to contemporary debate.',
                'feature_2_title' => 'Personal academic support',
                'feature_2_description' => 'An accessible teaching team supports every student throughout their studies.',
                'feature_3_title' => 'Career readiness',
                'feature_3_description' => 'Links with institutions, courts and employers prepare students for professional life.',
            ],
            'programs' => ['footer_label' => 'Qualifications and levels:'],
            'stats' => [
                'stat_1_label' => 'Degree programmes',
                'stat_2_label' => 'Degree levels',
                'stat_3_label' => 'A vibrant university community',
                'stat_4_label' => 'Public University of Mahajanga',
            ],
            'admissions' => [
                'step_1_title' => 'Check the requirements',
                'step_1_description' => 'Check the entry requirements for your chosen programme in the current official notice.',
                'step_2_title' => 'Prepare your documents',
                'step_2_description' => 'Gather the documents required for the admission round and your chosen programme.',
                'step_3_title' => 'Submit your application',
                'step_3_description' => 'Complete the application form carefully and review your information.',
                'step_4_title' => 'Receive confirmation',
                'step_4_description' => 'Keep your application number and follow the next steps sent to you.',
                'info_text' => 'Application dates, schedules and required documents are updated regularly on this website.',
                'campaign_fallback_text' => 'View the current official application notice.',
                'campaign_link_text' => 'View notices',
                'secondary_button_text' => 'View admission requirements',
            ],
            'student_life' => [
                'item_1' => 'Academic activities', 'item_2' => 'Conferences',
                'item_3' => 'Student societies', 'item_4' => 'Cultural events',
                'item_5' => 'Academic support', 'item_6' => 'Career support',
                'secondary_media_label' => 'Conference', 'tertiary_media_label' => 'Student event',
            ],
            'partners' => ['partner_link_text' => 'Discover this partner'],
            'cta' => ['secondary_button_text' => 'Ask a question'],
        ];

        foreach ($settings as $sectionKey => $translatedSettings) {
            DB::table('page_sections')->where('section_key', $sectionKey)->get(['id', 'translations'])
                ->each(function (object $section) use ($translatedSettings): void {
                    $translations = json_decode($section->translations ?: '{}', true);
                    $translations = is_array($translations) ? $translations : [];
                    $translations['en'] = is_array($translations['en'] ?? null) ? $translations['en'] : [];
                    $translations['en']['settings'] = [
                        ...(is_array($translations['en']['settings'] ?? null) ? $translations['en']['settings'] : []),
                        ...$translatedSettings,
                    ];

                    DB::table('page_sections')->where('id', $section->id)->update([
                        'translations' => json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ]);
                });
        }
    }

    public function down(): void
    {
        DB::table('page_sections')->whereNotNull('translations')->get(['id', 'translations'])
            ->each(function (object $section): void {
                $translations = json_decode($section->translations, true);
                if (! is_array($translations) || ! isset($translations['en'])) return;
                unset($translations['en']['settings']);
                DB::table('page_sections')->where('id', $section->id)->update([
                    'translations' => json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);
            });
    }
};
