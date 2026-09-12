<?php

namespace App\Repositories;

use App\Models\Employee;
use App\Models\Shift;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Collection;

class EmployeeRepository
{
   public function paginate(string $companyId, array $filters): CursorPaginator
   {
      return Employee::query()
         ->where('company_id', $companyId)
         ->with(['branch:id,name', 'shift:id,name,start_time,end_time'])
         ->when($filters['search'] ?? null, fn($query, $search) => $query->where(function ($nested) use ($search): void {
            $nested->where('name', 'like', "%{$search}%")
               ->orWhere('nik', 'like', "%{$search}%")
               ->orWhere('phone', 'like', "%{$search}%");
         }))
         ->when($filters['role'] ?? null, fn($query, $role) => $query->where('role', $role))
         ->when($filters['status'] ?? null, fn($query, $status) => $query->where('status', $status))
         ->orderBy('created_at', 'desc')
         ->orderBy('id', 'desc')
         ->cursorPaginate($filters['per_page'] ?? 10)
         ->withQueryString();
   }

   public function summary(string $companyId): array
   {
      $query = Employee::query()->where('company_id', $companyId);

      return [
         'total' => (clone $query)->count(),
         'active' => (clone $query)->where('status', 'active')->count(),
         'cashiers' => (clone $query)->where('role', 'cashier')->where('status', 'active')->count(),
         'base_salary' => (int) (clone $query)->where('status', 'active')->sum('base_salary'),
      ];
   }

   public function branches(string $companyId): Collection
   {
      return \App\Models\Branch::query()
         ->where('company_id', $companyId)
         ->orderBy('name')
         ->get(['id', 'name']);
   }

   public function shifts(string $companyId): Collection
   {
      return Shift::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('start_time')->get(['id', 'name', 'start_time', 'end_time']);
   }
}
