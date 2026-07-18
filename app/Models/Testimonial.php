<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
    protected $guarded = [];

    protected $appends = ['photo_url'];

    protected function casts(): array
    {
        return ['is_visible' => 'boolean'];
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'photo_id');
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo?->image_url;
    }
}
