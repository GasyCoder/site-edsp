<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesResource;

class PagePolicy
{
    use AuthorizesResource;

    protected function resource(): string
    {
        return 'pages';
    }
}
