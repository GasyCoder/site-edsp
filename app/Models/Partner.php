<?php

namespace App\Models;

use App\Rules\SafeUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Validator;

class Partner extends Model
{
    protected $guarded = [];

    protected $appends = ['logo_url'];

    protected function casts(): array
    {
        return ['is_visible' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(fn (Partner $partner) => Validator::make(
            ['url' => $partner->url],
            ['url' => ['nullable', 'string', 'max:2048', new SafeUrl]],
        )->validate());
    }

    public function logo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'logo_id');
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo?->image_url;
    }
}
