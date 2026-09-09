<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('roles.view');
    }

    public function create(User $user): bool
    {
        return $user->can('roles.create');
    }

    public function update(User $user, Role $role): bool
    {
        return $role->name !== 'owner'
            && $user->can('roles.edit')
            && $user->company_id === $role->company_id;
    }

    public function delete(User $user, Role $role): bool
    {
        return $role->name !== 'owner'
            && $user->can('roles.delete')
            && $user->company_id === $role->company_id;
    }
}
