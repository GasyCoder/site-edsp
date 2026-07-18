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
            ['parent_institution', null, 'string', 'general', true],
            ['site_description', 'Formations universitaires en droit et science politique à Mahajanga.', 'text', 'general', true],
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
            ['academic_year', now()->year.'-'.(now()->year + 1), 'string', 'academic', true],
            ['seo_default_title', 'EDSP — École de Droit et Sciences Politique', 'string', 'seo', true],
            ['seo_default_description', 'Découvrez les formations, actualités et admissions de l’École de Droit et Sciences Politique.', 'text', 'seo', true],
            ['seo_default_og_image', null, 'string', 'seo', true],
            ['default_meta_title', 'EDSP — École de Droit et Sciences Politique', 'string', 'seo', true],
            ['default_meta_description', 'Découvrez les formations, actualités et admissions de l’École de Droit et Sciences Politique.', 'text', 'seo', true],
            ['default_meta_keywords', 'EDSP, droit, science politique, Licence, Master, Mahajanga, Madagascar', 'text', 'seo', true],
            ['default_og_image', null, 'string', 'seo', true],
            ['logo_url', null, 'string', 'general', true],
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
