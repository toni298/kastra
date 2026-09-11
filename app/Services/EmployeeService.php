<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeService
{
   public function create(string $companyId, array $data): Employee
   {
      return DB::transaction(function () use ($companyId, $data): Employee {
         $attributes = Arr::except($data, ['pin']);
         $attributes['company_id'] = $companyId;
         $attributes['pin'] = Hash::make($data['pin']);

         return Employee::create($attributes);
      });
   }

   public function update(Employee $employee, array $data): Employee
   {
      return DB::transaction(function () use ($employee, $data): Employee {
         $attributes = Arr::except($data, ['pin']);
         if (filled($data['pin'] ?? null)) {
            $attributes['pin'] = Hash::make($data['pin']);
         }
         $employee->update($attributes);

         return $employee->refresh();
      });
   }

   public function delete(Employee $employee): void
   {
      DB::transaction(fn() => $employee->delete());
   }

   public function resetPin(Employee $employee, string $pin): Employee
   {
      $employee->update(['pin' => Hash::make($pin)]);

      return $employee->refresh();
   }
}
