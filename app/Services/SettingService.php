<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class SettingService
{
    public function public(): array
    {
        $settings = Cache::rememberForever('settings.public', function (): array {
            $settings = DB::table('settings')->where('is_public', true)->get(['key', 'value', 'type'])
                ->mapWithKeys(fn ($setting) => [$setting->key => $this->cast($setting->value, $setting->type)])
                ->all();

            foreach ([
                'address' => 'contact_address', 'email' => 'contact_email', 'phone' => 'contact_phone',
                'facebook' => 'facebook_url', 'linkedin' => 'linkedin_url', 'youtube' => 'youtube_url',
                'default_meta_title' => 'seo_default_title',
                'default_meta_description' => 'seo_default_description',
                'default_og_image' => 'seo_default_og_image',
            ] as $alias => $source) {
                if (blank($settings[$alias] ?? null)) {
                    $settings[$alias] = $settings[$source] ?? null;
                }
            }

            return $settings;
        });

        if (app()->isLocale('en')) {
            foreach (['institution_name', 'site_description', 'footer_text', 'ministerial_reference_label', 'ministerial_reference', 'accreditation_reference_label', 'accreditation_reference', 'default_meta_title', 'default_meta_description', 'default_meta_keywords'] as $key) {
                if (filled($settings[$key.'_en'] ?? null)) {
                    $settings[$key] = $settings[$key.'_en'];
                }
            }
        }

        return $settings;
    }

    public function forget(): void
    {
        Cache::forget('settings.public');
    }

    private function cast(?string $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'json' => json_decode((string) $value, true),
            default => $value,
        };
    }
}
