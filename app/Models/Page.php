<?php

namespace App\Models;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $appends = ['og_image_url'];

    protected function casts(): array
    {
        return ['status' => ContentStatus::class, 'published_at' => 'datetime', 'robots_index' => 'boolean', 'robots_follow' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::creating(function (Page $page): void {
            $page->created_by ??= auth()->id();
            $page->updated_by ??= auth()->id();
        });
        static::updating(fn (Page $page) => $page->updated_by = auth()->id() ?? $page->updated_by);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(PageSection::class)->orderBy('position');
    }

    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_image_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getOgImageUrlAttribute(): ?string
    {
        return $this->ogImage?->image_url;
    }

    public function revisions(): MorphMany
    {
        return $this->morphMany(ContentRevision::class, 'revisionable')->latest();
    }

    public function scopePublished($q)
    {
        return $q->where('status', 'published')->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }
}
