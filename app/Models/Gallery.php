<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gallery extends Model
{
    use HasLocalizedContent, SoftDeletes;

    protected array $translatable = ['title', 'description'];

    protected $guarded = [];

    protected $appends = ['cover_image_url'];

    protected function casts(): array
    {
        return ['is_visible' => 'boolean', 'published_at' => 'datetime', 'translations' => 'array'];
    }

    public function coverImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_image_id');
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->coverImage?->image_url;
    }

    public function images(): HasMany
    {
        return $this->hasMany(GalleryImage::class)->orderBy('position');
    }

    public function news(): MorphToMany
    {
        return $this->morphedByMany(News::class, 'galleryable');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')->where('is_visible', true)
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }
}
