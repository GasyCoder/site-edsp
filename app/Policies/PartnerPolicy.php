<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesResource;

class PartnerPolicy
{
    use AuthorizesResource;

    protected function resource(): string
    {
        return 'partners';
    }
}
