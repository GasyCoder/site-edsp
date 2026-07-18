<?php

namespace App\Policies\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait AuthorizesResource
{
    abstract protected function resource(): string;

    public function viewAny(User $user): bool
    {
        return $user->can('view '.$this->resource());
    }

    public function view(User $user, Model $record): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('create '.$this->resource());
    }

    public function update(User $user, Model $record): bool
    {
        return $user->can('edit '.$this->resource());
    }

    public function publish(User $user, Model $record): bool
    {
        return $user->can('publish '.$this->resource());
    }

    public function delete(User $user, Model $record): bool
    {
        return $user->can('delete '.$this->resource());
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete '.$this->resource());
    }

    public function restore(User $user, Model $record): bool
    {
        return $user->can('edit '.$this->resource());
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('edit '.$this->resource());
    }

    public function forceDelete(User $user, Model $record): bool
    {
        return $user->can('delete '.$this->resource());
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('delete '.$this->resource());
    }

    public function reorder(User $user): bool
    {
        return $user->can('edit '.$this->resource());
    }
}
