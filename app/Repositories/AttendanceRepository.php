<?php

namespace App\Repositories;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shift;
use Illuminate\Contracts\Pagination\CursorPaginator;

class AttendanceRepository
{
   public function ensureDailyRecords(string $companyId, string $date): void
   {
      Employee::query()->where('company_id', $companyId)->where('status', 'active')->each(function (Employee $employee) use ($companyId, $date): void {
         Attendance::query()->firstOrCreate(
            ['employee_id' => $employee->id, 'attendance_date' => $date],
            ['company_id' => $companyId, 'shift_id' => $employee->shift_id, 'status' => 'absent']
         );
      });
   }

   public function paginate(string $companyId, array $filters): CursorPaginator
   {
      return Attendance::query()
         ->where('attendances.company_id', $companyId)
         ->whereDate('attendance_date', $filters['date'] ?? now()->toDateString())
         ->with(['employee:id,name,nik', 'shift:id,name,start_time,end_time'])
         ->when($filters['search'] ?? null, fn($query, $search) => $query->whereHas('employee', fn($employee) => $employee->where('name', 'like', "%{$search}%")->orWhere('nik', 'like', "%{$search}%")))
         ->when($filters['status'] ?? null, fn($query, $status) => $query->where('status', $status))
         ->orderByDesc('attendance_date')->orderByDesc('id')
         ->cursorPaginate($filters['per_page'] ?? 10)->withQueryString();
   }

   public function summary(string $companyId, string $date): array
   {
      $query = Attendance::query()->where('company_id', $companyId)->whereDate('attendance_date', $date);

      return [
         'present' => (clone $query)->where('status', 'present')->count(),
         'late' => (clone $query)->where('status', 'late')->count(),
         'absent' => (clone $query)->where('status', 'absent')->count(),
         'overtime_hours' => 0,
      ];
   }

   public function shifts(string $companyId)
   {
      return Shift::query()
         ->where('company_id', $companyId)
         ->select(['id', 'name', 'start_time', 'end_time', 'grace_period_minutes', 'is_active'])
         ->withCount('employees')
         ->orderBy('start_time')
         ->get();
   }
}
