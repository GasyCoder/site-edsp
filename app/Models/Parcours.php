<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Parcours extends Model
{
    use HasLocalizedContent, SoftDeletes;

    protected array $translatable = ['nom', 'description'];

    protected $table = 'parcours';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['translations' => 'array'];
    }

    public function mention(): BelongsTo
    {
        return $this->belongsTo(Mention::class);
    }

    public function levelLinks(): HasMany
    {
        return $this->hasMany(ParcoursLevel::class);
    }
}
