<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesResource;

class NewsCategoryPolicy
{
    use AuthorizesResource;

    protected function resource(): string
    {
        return 'news';
    }
}
