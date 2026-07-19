<?php

namespace App\Models;

use App\Casts\SanitizedHtml;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class NewsletterCampaign extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'content' => SanitizedHtml::class,
            'event_starts_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'paused_at' => 'datetime',
            'started_at' => 'datetime',
            'sent_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updated(function (NewsletterCampaign $campaign): void {
            $oldPath = $campaign->getOriginal('attachment_path');
            $oldDisk = $campaign->getOriginal('attachment_disk') ?: 'private';

            if (filled($oldPath) && $oldPath !== $campaign->attachment_path) {
                DB::afterCommit(fn () => Storage::disk($oldDisk)->delete($oldPath));
            }
        });

        static::deleting(function (NewsletterCampaign $campaign): void {
            if (filled($campaign->attachment_path)) {
                $disk = $campaign->attachment_disk ?: 'private';
                $path = $campaign->attachment_path;
                DB::afterCommit(fn () => Storage::disk($disk)->delete($path));
            }
        });
    }

    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function statusChangedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'status_changed_by');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(NewsletterDelivery::class);
    }

    public function scopeDue($query)
    {
        return $query
            ->where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now());
    }

    public function canBePrepared(): bool
    {
        return in_array($this->status, ['draft', 'scheduled', 'failed', 'paused'], true);
    }
}
