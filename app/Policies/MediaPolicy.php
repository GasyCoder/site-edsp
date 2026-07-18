<?php

namespace App\Policies;

use App\Models\Media;
use App\Models\User;
use App\Policies\Concerns\AuthorizesResource;

class MediaPolicy
{
    use AuthorizesResource;

    protected function resource(): string
    {
        return 'media';
    }

    public function create(User $user): bool
    {
        return $user->can('upload media');
    }

    public function update(User $user, Media $media): bool
    {
        return $user->can('edit media');
    }
}
