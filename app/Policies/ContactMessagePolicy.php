<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesResource;

class ContactMessagePolicy
{
    use AuthorizesResource;

    protected function resource(): string
    {
        return 'contacts';
    }
}
