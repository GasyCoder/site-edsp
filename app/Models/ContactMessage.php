<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['consent' => 'boolean', 'read_at' => 'datetime', 'handled_at' => 'datetime', 'archived_at' => 'datetime'];
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function handler(): BelongsTo
    {
        return $this->handledBy();
    }
}
