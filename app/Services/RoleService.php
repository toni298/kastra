<?php

namespace App\Services;

use App\Models\Role;
use App\Services\InertiaAuthorizationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

class RoleService
{
    public function create(string $companyId, array $data): Role
    {
        return DB::transaction(function () use ($companyId, $data) {
            $role = Role::query()->create([
                'company_id' => $companyId,
                'name' => $data['name'],
                'guard_name' => 'web',
            ]);
            $role->syncPermissions($data['permissions']);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            $this->forgetAuthorizationSnapshots($role);

            return $role;
        });
    }

    public function update(Role $role, array $data): Role
    {
        return DB::transaction(function () use ($role, $data) {
            $role->update(['name' => $data['name']]);
            $role->syncPermissions($data['permissions']);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            $this->forgetAuthorizationSnapshots($role);

            return $role->refresh();
        });
    }

    public function delete(Role $role): void
    {
        if ($role->users()->exists()) {
            throw ValidationException::withMessages([
                'role' => 'Role masih digunakan pengguna dan tidak dapat dihapus.',
            ]);
        }

        DB::transaction(function () use ($role) {
            $role->delete();
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        });
    }

    private function forgetAuthorizationSnapshots(Role $role): void
    {
        $authorization = app(InertiaAuthorizationService::class);
        $role->loadMissing('users');

        foreach ($role->users as $user) {
            $authorization->forget($user);
        }
    }
}
