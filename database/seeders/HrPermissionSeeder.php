<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Services\RbacService;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class HrPermissionSeeder extends Seeder
{
   private array $permissions = [
      'hr.employees.view',
      'hr.employees.create',
      'hr.employees.edit',
      'hr.employees.delete',
      'hr.attendance.view',
      'hr.attendance.edit',
      'hr.attendance.clock',
      'hr.shifts.manage',
      'hr.commissions.view',
      'hr.overtime.view',
      'hr.overtime.create',
      'hr.overtime.approve',
      'hr.payroll.view',
      'hr.payroll.generate',
      'hr.payroll.post',
   ];

   public function run(RbacService $rbac): void
   {
      app(PermissionRegistrar::class)->forgetCachedPermissions();

      foreach ($this->permissions as $permission) {
         Permission::updateOrCreate(
            [
               'name' => $permission,
               'guard_name' => 'web',
            ],
            [
               'group' => 'SDM / HR',
               'subgroup' => 'Karyawan',
               'sort_order' => 1,
            ],
         );
      }

      // Role permissions must follow each company's selected features.
      $rbac->bootstrap();

      app(PermissionRegistrar::class)->forgetCachedPermissions();
   }
}
