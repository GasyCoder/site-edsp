<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeachingUnit extends Model
{
    use SoftDeletes;

    protected $table = 'ues';

    protected $guarded = [];

    protected $appends = ['label'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected function label(): Attribute
    {
        return Attribute::get(fn (): string => trim($this->code.' — '.$this->nom));
    }

    public function parcoursLevel(): BelongsTo
    {
        return $this->belongsTo(ParcoursLevel::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'semestre_id');
    }

    public function courseElements(): HasMany
    {
        return $this->hasMany(CourseElement::class, 'ue_id');
    }
}
