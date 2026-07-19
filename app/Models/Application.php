<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Application extends Model
{
    protected $guarded = [];

    protected $hidden = ['internal_notes'];

    protected function casts(): array
    {
        return ['birth_date' => 'date', 'submitted_at' => 'datetime', 'privacy_accepted' => 'boolean', 'status' => ApplicationStatus::class];
    }

    protected static function booted(): void
    {
        static::deleting(function (Application $application): void {
            $documents = $application->documents()
                ->get(['disk', 'path'])
                ->map(fn (ApplicationDocument $document): array => [
                    'disk' => $document->disk,
                    'path' => $document->path,
                ])
                ->all();

            DB::afterCommit(function () use ($documents): void {
                foreach ($documents as $document) {
                    Storage::disk($document['disk'])->delete($document['path']);
                }
            });
        });
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(AdmissionCampaign::class, 'admission_campaign_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function academicLevel(): BelongsTo
    {
        return $this->belongsTo(AcademicLevel::class, 'academic_level_id');
    }

    public function mention(): BelongsTo
    {
        return $this->belongsTo(Mention::class);
    }

    public function parcours(): BelongsTo
    {
        return $this->belongsTo(Parcours::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class)->latest();
    }

    public function statusHistories(): HasMany
    {
        return $this->statusHistory();
    }
}
