<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
   public function up(): void
   {
      $permissions = ['cabang.view', 'cabang.create', 'cabang.edit', 'cabang.delete', 'outlet.view', 'outlet.create', 'outlet.edit', 'outlet.delete', 'gudang.view', 'gudang.create', 'gudang.edit', 'gudang.delete'];
      foreach ($permissions as $permission) Permission::findOrCreate($permission, 'web');
      foreach (['owner', 'admin', 'karyawan'] as $role) Role::findOrCreate($role, 'web');
      Role::findByName('owner', 'web')->givePermissionTo($permissions);
   }
   public function down(): void
   {
      Permission::query()->whereIn('name', ['cabang.view', 'cabang.create', 'cabang.edit', 'cabang.delete', 'outlet.view', 'outlet.create', 'outlet.edit', 'outlet.delete', 'gudang.view', 'gudang.create', 'gudang.edit', 'gudang.delete'])->delete();
   }
};
