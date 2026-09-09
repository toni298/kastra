<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Services\InertiaAuthorizationService;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class CashierPermissionSeeder extends Seeder
{
   private array $permissions = [
      'cashier.access',
   ];

   public function run(): void
   {
      app(PermissionRegistrar::class)->forgetCachedPermissions();

      foreach ($this->permissions as $permission) {
         Permission::findOrCreate($permission, 'web');
      }

      $this->giveToRoles(['owner', 'admin', 'kasir'], $this->permissions);

      app(PermissionRegistrar::class)->forgetCachedPermissions();
   }

   private function giveToRoles(array $roleNames, array $permissions): void
   {
      Role::query()
         ->whereIn('name', $roleNames)
         ->where('guard_name', 'web')
         ->each(function (Role $role) use ($permissions): void {
            $role->givePermissionTo($permissions);
            $role->loadMissing('users');

            foreach ($role->users as $user) {
               app(InertiaAuthorizationService::class)->forget($user);
            }
         });
   }
}
