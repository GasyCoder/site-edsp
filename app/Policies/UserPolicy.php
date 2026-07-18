<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view users');
    }

    public function view(User $user, User $record): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('create users');
    }

    public function update(User $user, User $record): bool
    {
        return $user->can('edit users')
            && (! $record->hasRole('superadmin') || $user->hasRole('superadmin'));
    }

    public function delete(User $user, User $record): bool
    {
        if (! $user->can('delete users') || $user->is($record)) {
            return false;
        }

        return ! $record->hasRole('superadmin') || User::role('superadmin')->count() > 1;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
