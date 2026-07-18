<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesResource;

class DocumentPolicy
{
    use AuthorizesResource;

    protected function resource(): string
    {
        return 'documents';
    }
}
