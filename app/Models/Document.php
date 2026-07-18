<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $hidden = ['disk', 'path'];

    protected $appends = ['download_url'];

    protected function casts(): array
    {
        return ['is_public' => 'boolean', 'published_at' => 'datetime', 'size' => 'integer'];
    }

    protected static function booted(): void
    {
        static::forceDeleted(fn (Document $document) => DB::afterCommit(
            fn () => Storage::disk($document->disk)->delete($document->path)
        ));
    }

    public function getDownloadUrlAttribute(): ?string
    {
        return $this->is_public && $this->status === 'published'
            ? route('documents.download', $this)
            : null;
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
