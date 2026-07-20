<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParcoursLevel extends Model
{
    protected $guarded = [];

    protected $appends = ['label'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'is_common_core' => 'boolean'];
    }

    protected function label(): Attribute
    {
        return Attribute::get(fn (): string => trim(($this->parcours?->nom ?? '').' — '.($this->level?->code ?? '')));
    }

    public function parcours(): BelongsTo
    {
        return $this->belongsTo(Parcours::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(AcademicLevel::class, 'level_id');
    }

    public function teachingUnits(): HasMany
    {
        return $this->hasMany(TeachingUnit::class);
    }

    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'program_parcours_level');
    }
}
