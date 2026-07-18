<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseElement extends Model
{
    use SoftDeletes;

    protected $table = 'ecs';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'coefficient' => 'decimal:2',
            'is_active' => 'boolean',
            'is_historical_marker' => 'boolean',
        ];
    }

    public function teachingUnit(): BelongsTo
    {
        return $this->belongsTo(TeachingUnit::class, 'ue_id');
    }

    public function replacedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'replaced_by_ec_id');
    }
}
