<?php

namespace App\Models;

use App\Casts\SanitizedHtml;
use App\Models\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasLocalizedContent;

    protected array $translatable = ['question', 'answer', 'category'];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'answer' => SanitizedHtml::class,
            'is_visible' => 'boolean',
            'position' => 'integer',
            'translations' => 'array',
        ];
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }
}
