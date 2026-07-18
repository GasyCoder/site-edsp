<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesResource;

class AcademicPolicy
{
    use AuthorizesResource;

    protected function resource(): string
    {
        return 'academic';
    }
}
