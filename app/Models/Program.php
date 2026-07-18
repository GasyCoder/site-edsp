<?php

namespace App\Models;

use App\Casts\SanitizedHtml;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $appends = ['image_url', 'og_image_url'];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
            'description' => SanitizedHtml::class,
            'objectives' => SanitizedHtml::class,
            'admission_requirements' => SanitizedHtml::class,
            'skills' => SanitizedHtml::class,
            'careers' => SanitizedHtml::class,
            'curriculum' => SanitizedHtml::class,
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image?->image_url;
    }

    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_image_id');
    }

    public function getOgImageUrlAttribute(): ?string
    {
        return $this->ogImage?->image_url;
    }

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(AdmissionCampaign::class);
    }

    public function documents(): MorphToMany
    {
        return $this->morphToMany(Document::class, 'documentable');
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
