<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class ProductPermissionSeeder extends Seeder
{
   private array $permissions = [
      'products.view',
      'products.create',
      'products.edit',
      'products.delete',
      'product_categories.view',
      'product_categories.create',
      'product_categories.edit',
      'product_categories.delete',
      'product_brands.view',
      'product_brands.create',
      'product_brands.edit',
      'product_brands.delete',
      'units.view',
      'units.create',
      'units.edit',
      'units.delete',
   ];

   private array $obsoletePermissions = ['products.images'];

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
      $this->giveToRoles(['karyawan'], [
         'products.view',
         'product_categories.view',
         'product_brands.view',
         'units.view',
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
