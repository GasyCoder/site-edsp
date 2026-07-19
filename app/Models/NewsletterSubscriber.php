<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsletterSubscriber extends Model
{
    protected $guarded = [];

    protected $appends = ['subscription_status'];

    protected function casts(): array
    {
        return [
            'verification_sent_at' => 'datetime',
            'verified_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
            'imported_at' => 'datetime',
        ];
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(NewsletterDelivery::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNotNull('verified_at')->whereNull('unsubscribed_at');
    }

    public function getSubscriptionStatusAttribute(): string
    {
        if ($this->unsubscribed_at !== null) {
            return 'unsubscribed';
        }

        return $this->verified_at !== null ? 'active' : 'pending';
    }
}
