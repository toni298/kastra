<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class SalesPermissionSeeder extends Seeder
{
   private array $permissions = [
      'penjualan.summary.view',
      'penjualan.transactions.view',
      'penjualan.transactions.create',
      'penjualan.transactions.edit',
      'penjualan.transactions.delete',
      'penjualan.transactions.return',
      'penjualan.transactions.print',
      'penjualan.transactions.payment',
      'penjualan.returns.view',
      'penjualan.customers.view',
      'penjualan.customers.create',
      'penjualan.customers.edit',
      'penjualan.customers.delete',
   ];

   public function run(): void
   {
      app(PermissionRegistrar::class)->forgetCachedPermissions();
      foreach ($this->permissions as $permission) Permission::findOrCreate($permission, 'web');
      $this->giveToRoles(['owner', 'admin'], $this->permissions);
      $this->giveToRoles(['karyawan'], ['penjualan.summary.view', 'penjualan.transactions.view', 'penjualan.returns.view', 'penjualan.customers.view']);
      app(PermissionRegistrar::class)->forgetCachedPermissions();
   }

   private function giveToRoles(array $roleNames, array $permissions): void
   {
      Role::query()->whereIn('name', $roleNames)->where('guard_name', 'web')->each(fn(Role $role) => $role->givePermissionTo($permissions));
   }
}
