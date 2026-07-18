<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamMember extends Model
{
    protected $guarded = [];

    protected $appends = ['full_name', 'photo_url'];

    protected function casts(): array
    {
        return ['social_links' => 'array', 'is_visible' => 'boolean'];
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'photo_id');
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo?->image_url;
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')->where('is_visible', true);
    }
}
