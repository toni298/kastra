<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }
    public function create(User $user): bool
    {
        return $user->can('users.create');
    }
    public function update(User $user, User $target): bool
    {
        return $user->can('users.edit') && $user->company_id === $target->company_id;
    }

    public function delete(User $user, User $target): bool
    {
        return $user->isNot($target)
            && $user->can('users.delete')
            && $user->company_id === $target->company_id
            && ! $target->roles()->where('name', 'owner')->exists();
    }
}
