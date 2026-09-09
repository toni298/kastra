<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class CompanyPermissionSeeder extends Seeder
{
   private array $permissions = [
      'company.view',
      'company.edit',
      'company.settings',
      'company.logo',
      'taxes.view',
      'taxes.create',
      'cabang.view',
      'cabang.create',
      'cabang.edit',
      'cabang.delete',
      'gudang.view',
      'gudang.create',
      'gudang.edit',
      'gudang.delete',
   ];

   public function run(): void
   {
      app(PermissionRegistrar::class)->forgetCachedPermissions();

      foreach ($this->permissions as $permission) {
         Permission::findOrCreate($permission, 'web');
      }

      $this->giveToRoles(['owner', 'admin'], $this->permissions);
      $this->giveToRoles(['karyawan'], [
         'company.view',
         'taxes.view',
         'cabang.view',
         'gudang.view',
      ]);

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
