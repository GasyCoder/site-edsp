<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesResource;

class GalleryPolicy
{
    use AuthorizesResource;

    protected function resource(): string
    {
        return 'galleries';
    }
}
