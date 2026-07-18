<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class SettingService
{
    public function public(): array
    {
        return Cache::rememberForever('settings.public', function (): array {
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
