<?php

namespace App\Models;

use App\Casts\SanitizedHtml;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AdmissionCampaign extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['opens_at' => 'datetime', 'closes_at' => 'datetime', 'required_documents' => 'array', 'is_visible' => 'boolean', 'instructions' => SanitizedHtml::class];
    }

    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /** @return list<array{key: string, label: string, required: bool}> */
    public function normalizedRequiredDocuments(): array
    {
        return collect($this->required_documents ?? [])
            ->map(function (mixed $document): array {
                $label = is_array($document)
                    ? (string) ($document['label'] ?? 'Document demandé')
                    : (string) $document;

                return [
                    'key' => is_array($document) && filled($document['key'] ?? null)
                        ? Str::slug((string) $document['key'])
                        : Str::slug($label),
                    'label' => $label,
                    'required' => ! is_array($document) || ($document['required'] ?? true),
                ];
            })
            ->filter(fn (array $document): bool => filled($document['key']) && filled($document['label']))
            ->values()
            ->all();
    }

    public function scopeOpen($q)
    {
        return $q->where('status', 'published')->where('is_visible', true)->where('opens_at', '<=', now())->where('closes_at', '>=', now());
    }
}
