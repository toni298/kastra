<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class PurchasesPermissionSeeder extends Seeder
{
   private array $permissions = [
      'pembelian.view',
      'pembelian.create',
      'pembelian.edit',
      'pembelian.delete',
      'pembelian.return',
      'pembelian.print',
      'pembelian.complete',
      'pembelian.pay',
      'pembelian.returns.view',
   ];

   public function run(): void
   {
      app(PermissionRegistrar::class)->forgetCachedPermissions();

      foreach ($this->permissions as $permission) {
         Permission::findOrCreate($permission, 'web');
      }

      $this->giveToRoles(['owner', 'admin'], $this->permissions);
      $this->giveToRoles(['karyawan'], ['pembelian.view', 'pembelian.returns.view']);

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
