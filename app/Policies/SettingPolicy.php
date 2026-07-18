<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesResource;

class SettingPolicy
{
    use AuthorizesResource;

    protected function resource(): string
    {
        return 'settings';
    }
}
