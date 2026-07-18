<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesResource;

class TeamMemberPolicy
{
    use AuthorizesResource;

    protected function resource(): string
    {
        return 'team';
    }
}
