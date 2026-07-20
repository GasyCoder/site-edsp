<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mention extends Model
{
    use HasLocalizedContent, SoftDeletes;

    protected array $translatable = ['nom', 'description'];

    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'translations' => 'array'];
    }

    public function parcours(): HasMany
    {
        return $this->hasMany(Parcours::class);
    }

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }
}
