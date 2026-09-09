<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class InventoryPermissionSeeder extends Seeder
{
   private array $permissions = [
      'inventory.summary.view',
      'inventory.summary.discount',
      'inventory.summary.adjust',
      'inventory.stock.view',
      'inventory.stock.create',
      'inventory.stock.edit',
      'inventory.stock.delete',
      'inventory.transfers.view',
      'inventory.transfers.create',
      'inventory.transfers.edit',
      'inventory.transfers.delete',
      'inventory.transfers.receive',
      'inventory.opname.view',
      'inventory.opname.create',
      'inventory.opname.edit',
      'inventory.opname.delete',
      'inventory.opname.process',
   ];

   public function run(): void
   {
      app(PermissionRegistrar::class)->forgetCachedPermissions();

      foreach ($this->permissions as $permission) {
         Permission::findOrCreate($permission, 'web');
      }

      $this->giveToRoles(['owner', 'admin'], $this->permissions);
      $this->giveToRoles(['karyawan'], [
         'inventory.summary.view',
         'inventory.stock.view',
         'inventory.transfers.view',
         'inventory.opname.view',
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
