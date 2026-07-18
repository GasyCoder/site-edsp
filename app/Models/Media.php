<?php

namespace App\Models;

use App\Services\MediaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $table = 'media';

    protected $guarded = [];

    protected $appends = ['url', 'image_url', 'thumbnail_url'];

    protected function casts(): array
    {
        return ['size' => 'integer', 'width' => 'integer', 'height' => 'integer'];
    }

    public function getUrlAttribute(): ?string
    {
        return $this->disk === 'public' ? Storage::disk($this->disk)->url($this->path) : null;
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->disk === 'public' && filled($this->optimized_path)) {
            return Storage::disk($this->disk)->url($this->optimized_path);
        }

        return $this->url;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->disk === 'public' && filled($this->thumbnail_path)) {
            return Storage::disk($this->disk)->url($this->thumbnail_path);
        }

        return $this->image_url;
    }

    protected static function booted(): void
    {
        static::deleting(fn (Media $media) => app(MediaService::class)->ensureUnused($media));
        static::deleted(fn (Media $media) => DB::afterCommit(
            fn () => Storage::disk($media->disk)->delete(array_filter([
                $media->path,
                $media->optimized_path,
                $media->thumbnail_path,
            ]))
        ));
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function galleryImages(): HasMany
    {
        return $this->hasMany(GalleryImage::class);
    }
}
