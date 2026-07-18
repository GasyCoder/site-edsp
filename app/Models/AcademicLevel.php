<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicLevel extends Model
{
    use SoftDeletes;

    protected $table = 'levels';

    protected $guarded = [];

    public function parcoursLinks(): HasMany
    {
        return $this->hasMany(ParcoursLevel::class, 'level_id');
    }
}
