<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasLocalizedContent, SoftDeletes;

    protected array $translatable = ['title', 'description', 'category'];

    protected $guarded = [];

    protected $hidden = ['disk', 'path'];

    protected $appends = ['download_url', 'preview_url'];

    protected function casts(): array
    {
        return ['is_public' => 'boolean', 'published_at' => 'datetime', 'size' => 'integer', 'translations' => 'array'];
    }

    protected static function booted(): void
    {
        static::forceDeleted(fn (Document $document) => DB::afterCommit(
            fn () => Storage::disk($document->disk)->delete($document->path)
        ));
    }

    public function getDownloadUrlAttribute(): ?string
    {
        return $this->isPubliclyAvailable()
            ? route('documents.download', $this)
            : null;
    }

    public function getPreviewUrlAttribute(): ?string
    {
        return $this->isPubliclyAvailable() && $this->mime_type === 'application/pdf'
            ? route('documents.preview', $this)
            : null;
    }

    public function isPubliclyAvailable(): bool
    {
        return $this->is_public
            && $this->status === 'published'
            && ($this->published_at === null || $this->published_at->isPast());
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function news(): MorphToMany
    {
        return $this->morphedByMany(News::class, 'documentable');
    }

    public function programs(): MorphToMany
    {
        return $this->morphedByMany(Program::class, 'documentable');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }
}
