<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesResource;

class ProgramPolicy
{
    use AuthorizesResource;

    protected function resource(): string
    {
        return 'programs';
    }
}
