<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesResource;

class PageSectionPolicy
{
    use AuthorizesResource;

    protected function resource(): string
    {
        return 'pages';
    }
}
