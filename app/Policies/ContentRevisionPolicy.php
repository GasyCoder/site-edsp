<?php

namespace App\Policies;

use App\Models\ContentRevision;
use App\Models\User;
use App\Policies\Concerns\AuthorizesResource;

class ContentRevisionPolicy
{
    use AuthorizesResource;

    protected function resource(): string
    {
        return 'revisions';
    }

    public function restore(User $user, ContentRevision $revision): bool
    {
        return $user->can('restore revisions');
    }
}
