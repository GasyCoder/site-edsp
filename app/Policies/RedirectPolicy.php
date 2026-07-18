<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesResource;

class RedirectPolicy
{
    use AuthorizesResource;

    protected function resource(): string
    {
        return 'redirects';
    }
}
