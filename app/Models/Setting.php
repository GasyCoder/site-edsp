<?php

namespace App\Models;

use App\Rules\SafeUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class Setting extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_public' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (Setting $setting): void {
            if (in_array($setting->key, [
                'facebook', 'facebook_url', 'linkedin', 'linkedin_url', 'youtube', 'youtube_url',
                'library_url', 'logo_url', 'favicon_url', 'default_og_image', 'seo_default_og_image',
            ], true)) {
                Validator::make(['value' => $setting->value], [
                    'value' => ['nullable', 'string', 'max:2048', new SafeUrl],
                ])->validate();
            }
        });
        static::saved(function (): void {
            Cache::forget('settings.public');
            Cache::forget('settings.maintenance_mode');
        });
        static::deleted(function (): void {
            Cache::forget('settings.public');
            Cache::forget('settings.maintenance_mode');
        });
    }
}
