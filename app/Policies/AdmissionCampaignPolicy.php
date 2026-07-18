<?php

namespace App\Policies;

use App\Models\AdmissionCampaign;
use App\Models\User;
use App\Policies\Concerns\AuthorizesResource;

class AdmissionCampaignPolicy
{
    use AuthorizesResource;

    protected function resource(): string
    {
        return 'campaigns';
    }

    public function delete(User $user, AdmissionCampaign $record): bool
    {
        return $user->can('delete campaigns') && $record->applications()->doesntExist();
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
