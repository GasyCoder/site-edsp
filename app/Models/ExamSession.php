<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamSession extends Model
{
    use SoftDeletes;

    protected $table = 'sessions_examens';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_rattrapage' => 'boolean', 'is_active' => 'boolean'];
    }
}
