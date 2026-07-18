<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesResource;

class NewsPolicy
{
    use AuthorizesResource;

    protected function resource(): string
    {
        return 'news';
    }
}
