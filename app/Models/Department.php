<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasLocalizedContent;

    protected array $translatable = ['name', 'description'];

    protected $guarded = [];

    protected function casts(): array
    {
        return ['translations' => 'array'];
    }

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }
}
