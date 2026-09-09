<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class UserPermissionSeeder extends Seeder
{
   private array $obsoletePermissions = [
      'users.assign_role',
      'users.assign_permission',
   ];

   private array $permissions = [
      'users.view',
      'users.create',
      'users.edit',
      'users.delete',
   ];

   public function run(): void
   {
      app(PermissionRegistrar::class)->forgetCachedPermissions();

      Permission::query()
         ->whereIn('name', $this->obsoletePermissions)
         ->where('guard_name', 'web')
         ->get()
         ->each(fn(Permission $permission) => $permission->delete());

      foreach ($this->permissions as $permission) {
         Permission::findOrCreate($permission, 'web');
      }

      $this->giveToRoles(['owner', 'admin'], $this->permissions);
      $this->giveToRoles(['karyawan'], ['users.view']);

      app(PermissionRegistrar::class)->forgetCachedPermissions();
   }

   private function giveToRoles(array $roleNames, array $permissions): void
   {
      Role::query()
         ->whereIn('name', $roleNames)
         ->where('guard_name', 'web')
         ->each(fn(Role $role) => $role->givePermissionTo($permissions));
   }
}
