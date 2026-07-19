<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicLevel extends Model
{
    use HasLocalizedContent, SoftDeletes;

    protected array $translatable = ['nom'];

    protected $table = 'levels';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['translations' => 'array'];
    }

    public function parcoursLinks(): HasMany
    {
        return $this->hasMany(ParcoursLevel::class, 'level_id');
    }
}
