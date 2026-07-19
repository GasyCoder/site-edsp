<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsCategory extends Model
{
    use HasLocalizedContent;

    protected array $translatable = ['name'];

    protected $guarded = [];

    protected function casts(): array
    {
        return ['translations' => 'array'];
    }

    public function news(): HasMany
    {
        return $this->hasMany(News::class, 'category_id');
    }
}
