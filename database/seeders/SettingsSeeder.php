<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['site_name', 'EDSP', 'string', 'general', true],
            ['institution_name', 'École de Droit et Sciences Politique', 'string', 'general', true],
            ['institution_name_en', 'School of Law and Political Science', 'string', 'general', true],
            ['parent_institution', null, 'string', 'general', true],
            ['site_description', 'Formations universitaires en droit et science politique à Mahajanga.', 'text', 'general', true],
            ['site_description_en', 'University degree programmes in Law and Political Science in Mahajanga.', 'text', 'general', true],
            ['contact_email', 'edsp.mahajanga@gmail.com', 'string', 'contact', true],
            ['contact_phone', '+261 32 05 579 90', 'string', 'contact', true],
            ['phone_secondary', '+261 32 98 091 18', 'string', 'contact', true],
            ['contact_address', 'Campus universitaire d’Ambondrona, Mahajanga', 'string', 'contact', true],
            ['contact_location', 'Mahajanga, Madagascar', 'string', 'contact', true],
            ['address', 'Campus universitaire d’Ambondrona, Mahajanga', 'string', 'contact', true],
            ['email', 'edsp.mahajanga@gmail.com', 'string', 'contact', true],
            ['phone', '+261 32 05 579 90', 'string', 'contact', true],
            ['facebook_url', null, 'string', 'social', true],
            ['linkedin_url', null, 'string', 'social', true],
            ['youtube_url', null, 'string', 'social', true],
            ['facebook', null, 'string', 'social', true],
            ['linkedin', null, 'string', 'social', true],
            ['youtube', null, 'string', 'social', true],
            ['footer_text', 'École de Droit et Sciences Politique', 'text', 'general', true],
            ['footer_text_en', 'School of Law and Political Science', 'text', 'general', true],
            ['ministerial_reference_label', 'Référence ministérielle', 'string', 'legal', true],
            ['ministerial_reference', 'Arrêté n°8008/2014-MESupRES du 29 janvier 2014', 'string', 'legal', true],
            ['ministerial_reference_label_en', 'Ministerial reference', 'string', 'legal', true],
            ['ministerial_reference_en', 'Order No. 8008/2014-MESupRES of 29 January 2014', 'string', 'legal', true],
            ['accreditation_reference_label', 'Habilitation de l’offre de formation', 'string', 'legal', true],
            ['accreditation_reference', 'Arrêté n°34682/2025-MESUPRES portant habilitation de l’offre de formation dispensée par l’établissement d’enseignement supérieur dénommé « Université de Mahajanga – École de Droit et Science Politique – EDSP »', 'text', 'legal', true],
            ['accreditation_reference_label_en', 'Degree programme accreditation', 'string', 'legal', true],
            ['accreditation_reference_en', 'Order No. 34682/2025-MESUPRES accrediting the degree programmes delivered by the higher education institution known as “University of Mahajanga – School of Law and Political Science (EDSP)”', 'text', 'legal', true],
            ['academic_year', now()->year.'-'.(now()->year + 1), 'string', 'academic', true],
            ['seo_default_title', 'EDSP — École de Droit et Sciences Politique', 'string', 'seo', true],
            ['seo_default_description', 'Découvrez les formations, actualités et admissions de l’École de Droit et Sciences Politique.', 'text', 'seo', true],
            ['seo_default_og_image', null, 'string', 'seo', true],
            ['default_meta_title', 'EDSP — École de Droit et Sciences Politique', 'string', 'seo', true],
            ['default_meta_title_en', 'EDSP — School of Law and Political Science', 'string', 'seo', true],
            ['default_meta_description', 'Découvrez les formations, actualités et admissions de l’École de Droit et Sciences Politique.', 'text', 'seo', true],
            ['default_meta_description_en', 'Explore programmes, news and admissions at the School of Law and Political Science.', 'text', 'seo', true],
            ['default_meta_keywords', 'EDSP, droit, science politique, Licence, Master, Mahajanga, Madagascar', 'text', 'seo', true],
            ['default_meta_keywords_en', 'EDSP, law, political science, Bachelor, Master, Mahajanga, Madagascar', 'text', 'seo', true],
            ['default_og_image', null, 'string', 'seo', true],
            ['logo_url', null, 'string', 'general', true],
            ['logo_dark_url', '/images/logo-edsp-transparent.png', 'string', 'general', true],
            ['favicon_url', '/images/logo-edsp.png', 'string', 'general', true],
            ['library_url', null, 'string', 'general', true],
            ['robots_content', "User-agent: *\nAllow: /\nSitemap: /sitemap.xml", 'text', 'seo', true],
            ['maintenance_mode', 'false', 'boolean', 'system', false],
            ['legal_information', 'Informations légales à compléter par l’établissement.', 'text', 'legal', true],
        ];

        foreach ($settings as [$key, $value, $type, $group, $public]) {
            Setting::query()->updateOrCreate(['key' => $key], ['value' => $value, 'type' => $type, 'group' => $group, 'is_public' => $public]);
        }
    }
}
