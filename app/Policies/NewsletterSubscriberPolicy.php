<?php

namespace App\Policies;

use App\Models\NewsletterSubscriber;
use App\Models\User;
use App\Policies\Concerns\AuthorizesResource;

class NewsletterSubscriberPolicy
{
    use AuthorizesResource;

    protected function resource(): string
    {
        return 'newsletter subscribers';
    }

    public function confirm(User $user, NewsletterSubscriber $subscriber): bool
    {
        return $user->hasRole('superadmin');
    }

    public function reactivate(User $user, NewsletterSubscriber $subscriber): bool
    {
        return $user->hasRole('superadmin');
    }
}
