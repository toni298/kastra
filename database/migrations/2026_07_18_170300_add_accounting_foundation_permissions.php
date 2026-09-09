<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        $permissions = ['coa.view', 'coa.create', 'coa.edit', 'coa.delete', 'taxes.view', 'taxes.create', 'taxes.edit', 'taxes.delete', 'number_generators.view', 'number_generators.create', 'number_generators.edit', 'number_generators.delete'];
        foreach ($permissions as $permission) Permission::findOrCreate($permission, 'web');
        Role::findByName('owner', 'web')->givePermissionTo($permissions);
    }
    public function down(): void
    {
        Permission::query()->whereIn('name', ['coa.view', 'coa.create', 'coa.edit', 'coa.delete', 'taxes.view', 'taxes.create', 'taxes.edit', 'taxes.delete', 'number_generators.view', 'number_generators.create', 'number_generators.edit', 'number_generators.delete'])->delete();
    }
};
