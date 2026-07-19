<?php

namespace App\Models;

use App\Casts\ControlledSectionSettings;
use App\Casts\SanitizedHtml;
use App\Models\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PageSection extends Model
{
    use HasLocalizedContent;

    protected array $translatable = ['title', 'subtitle', 'content', 'button_text', 'settings'];
    protected $guarded = [];

    protected $appends = ['image_url'];

    protected function casts(): array
    {
        return ['settings' => ControlledSectionSettings::class, 'is_visible' => 'boolean', 'content' => SanitizedHtml::class, 'translations' => 'array'];
    }

    protected static function booted(): void
    {
        static::creating(function (PageSection $section): void {
            $section->created_by ??= auth()->id();
            $section->updated_by ??= auth()->id();
        });
        static::updating(fn (PageSection $section) => $section->updated_by = auth()->id() ?? $section->updated_by);
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image?->image_url;
    }

    public function revisions(): MorphMany
    {
        return $this->morphMany(ContentRevision::class, 'revisionable')->latest();
    }
}
