<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
   public function viewAny(User $user): bool
   {
      return $user->can('hr.employees.view');
   }

   public function create(User $user): bool
   {
      return $user->can('hr.employees.create');
   }

   public function update(User $user, Employee $employee): bool
   {
      return $user->can('hr.employees.edit') && $user->company_id === $employee->company_id;
   }

   public function delete(User $user, Employee $employee): bool
   {
      return $user->can('hr.employees.delete') && $user->company_id === $employee->company_id;
   }
}
