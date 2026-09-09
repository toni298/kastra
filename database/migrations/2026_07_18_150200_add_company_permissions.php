<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = ['company.view', 'company.edit', 'company.settings', 'company.logo'];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (['owner', 'admin', 'karyawan'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        Role::findByName('owner', 'web')->givePermissionTo($permissions);
    }

    public function down(): void
    {
        Permission::query()->whereIn('name', ['company.view', 'company.edit', 'company.settings', 'company.logo'])->delete();
    }
};
