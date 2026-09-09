<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class AccountingPermissionSeeder extends Seeder
{
   private array $permissions = [
      'accounting.view',
      'coa.view',
      'coa.create',
      'coa.edit',
      'coa.delete',
      'laporan.view',
      'laporan.export',
      'taxes.view',
      'taxes.create',
      'taxes.edit',
      'taxes.delete',
      'number_generators.view',
      'number_generators.create',
      'number_generators.edit',
      'number_generators.delete',
   ];

   public function run(): void
   {
      app(PermissionRegistrar::class)->forgetCachedPermissions();

      foreach ($this->permissions as $permission) {
         Permission::findOrCreate($permission, 'web');
      }

      $this->giveToRoles(['owner', 'admin'], $this->permissions);
      $this->giveToRoles(['karyawan'], ['laporan.view']);

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
