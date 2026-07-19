<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryImage extends Model
{
    use HasLocalizedContent;

    protected array $translatable = ['title', 'alt_text', 'caption'];
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_visible' => 'boolean', 'translations' => 'array'];
    }

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
