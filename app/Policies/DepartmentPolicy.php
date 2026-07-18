<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesResource;

class DepartmentPolicy
{
    use AuthorizesResource;

    protected function resource(): string
    {
        return 'programs';
    }
}
