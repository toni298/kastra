<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class CashBankPermissionSeeder extends Seeder
{
   private array $permissions = [
      'cash_bank.view',
      'cash_bank.transactions.view',
      'cash_bank.transactions.create',
      'cash_bank.transactions.edit',
      'cash_bank.transactions.delete',
      'cash_bank.accounts.view',
      'cash_bank.accounts.create',
      'cash_bank.accounts.edit',
      'cash_bank.accounts.delete',
      'cash_bank.accounts.deactivate',
      'cash_bank.transfers.view',
      'cash_bank.transfers.create',
      'cash_bank.settings.view',
      'cash_bank.settings.create',
      'cash_bank.settings.edit',
      'cash_bank.settings.delete',
   ];

   public function run(): void
   {
      app(PermissionRegistrar::class)->forgetCachedPermissions();

      foreach ($this->permissions as $permission) {
         Permission::findOrCreate($permission, 'web');
      }

      $this->giveToRoles(['owner', 'admin'], $this->permissions);
      $this->giveToRoles([
         'karyawan',
      ], [
         'cash_bank.view',
         'cash_bank.transactions.view',
         'cash_bank.transactions.create',
         'cash_bank.accounts.view',
         'cash_bank.transfers.view',
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
