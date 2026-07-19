<?php

namespace App\Policies;

use App\Models\NewsletterCampaign;
use App\Models\User;
use App\Policies\Concerns\AuthorizesResource;

class NewsletterCampaignPolicy
{
    use AuthorizesResource;

    protected function resource(): string
    {
        return 'newsletter campaigns';
    }

    public function send(User $user, NewsletterCampaign $campaign): bool
    {
        return $user->can('send newsletter campaigns');
    }

    public function delete(User $user, NewsletterCampaign $campaign): bool
    {
        return $user->can('delete newsletter campaigns')
            && ! in_array($campaign->status, ['sending', 'sent', 'event_cancelled'], true);
    }
}
