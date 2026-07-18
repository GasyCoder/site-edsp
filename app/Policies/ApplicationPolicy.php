<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;
use App\Policies\Concerns\AuthorizesResource;

class ApplicationPolicy
{
    use AuthorizesResource;

    protected function resource(): string
    {
        return 'applications';
    }

    public function changeStatus(User $user, Application $application): bool
    {
        return $user->can('change application status');
    }

    public function downloadDocuments(User $user, Application $application): bool
    {
        return $user->can('download application documents');
    }

    public function exportAny(User $user): bool
    {
        return $user->can('export applications');
    }
}
