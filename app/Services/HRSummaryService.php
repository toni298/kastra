<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Overtime;
use App\Models\PayrollItem;

class HRSummaryService
{
   public function build(string $companyId): array
   {
      $today = now()->toDateString();
      $monthStart = now()->startOfMonth()->toDateString();
      $monthEnd = now()->endOfMonth()->toDateString();
      $employeeQuery = Employee::query()->where('company_id', $companyId);

      $activeEmployees = (clone $employeeQuery)->where('status', 'active');
      $summary = [
         'total' => (clone $employeeQuery)->count(),
         'active' => (clone $activeEmployees)->count(),
         'cashiers' => (clone $activeEmployees)->where('role', 'cashier')->count(),
         'base_salary' => (int) (clone $activeEmployees)->sum('base_salary'),
      ];

      $attendanceCounts = Attendance::query()
         ->where('company_id', $companyId)
         ->whereDate('attendance_date', $today)
         ->selectRaw('status, COUNT(*) as total')
         ->groupBy('status')
         ->pluck('total', 'status');

      $onDuty = Attendance::query()
         ->where('attendances.company_id', $companyId)
         ->whereDate('attendance_date', $today)
         ->whereIn('status', ['present', 'late'])
         ->with(['employee:id,name,role', 'shift:id,name,start_time,end_time'])
         ->orderBy('clock_in')
         ->limit(5)
         ->get()
         ->map(fn (Attendance $attendance): array => [
            'id' => $attendance->id,
            'employee' => $attendance->employee?->name,
            'role' => $attendance->employee?->role,
            'shift' => $attendance->shift?->name,
            'clock_in' => $attendance->clock_in?->format('H:i'),
            'status' => $attendance->status,
         ])->values()->all();

      $pendingApprovals = Overtime::query()
         ->where('company_id', $companyId)
         ->where('status', 'pending')
         ->with('employee:id,name,role')
         ->orderBy('overtime_date')
         ->orderBy('created_at')
         ->limit(5)
         ->get()
         ->map(fn (Overtime $overtime): array => [
            'id' => $overtime->id,
            'type' => 'overtime',
            'employee' => $overtime->employee?->name,
            'date' => $overtime->overtime_date?->format('d/m/Y'),
            'hours' => (float) $overtime->hours,
            'amount' => (int) $overtime->total_amount,
            'status' => $overtime->status,
         ])->values()->all();

      $payrollProjection = PayrollItem::query()
         ->where('company_id', $companyId)
         ->whereHas('payroll', fn ($query) => $query->whereBetween('period', [$monthStart, $monthEnd]))
         ->selectRaw('COALESCE(SUM(basic_salary), 0) as basic, COALESCE(SUM(commission_amount), 0) as commission, COALESCE(SUM(overtime_amount), 0) as overtime')
         ->first();

      $topPerformers = PayrollItem::query()
         ->where('payroll_items.company_id', $companyId)
         ->whereHas('payroll', fn ($query) => $query->whereBetween('period', [$monthStart, $monthEnd]))
         ->with('employee:id,name,role')
         ->orderByDesc('commission_amount')
         ->limit(5)
         ->get(['id', 'employee_id', 'commission_amount', 'commission_detail'])
         ->map(fn (PayrollItem $item): array => [
            'employee' => $item->employee?->name,
            'role' => $item->employee?->role,
            'commission' => (int) $item->commission_amount,
            'sales' => (int) ($item->commission_detail['sales'] ?? 0),
            'qty' => (int) ($item->commission_detail['qty'] ?? 0),
         ])->values()->all();

      $contractExpiringSoon = (clone $employeeQuery)
         ->where('status', 'active')
         ->whereNotNull('contract_ends_at')
         ->whereBetween('contract_ends_at', [now()->toDateString(), now()->addDays(30)->toDateString()])
         ->orderBy('contract_ends_at')
         ->limit(5)
         ->get(['id', 'name', 'role', 'contract_ends_at'])
         ->map(fn (Employee $employee): array => [
            'id' => $employee->id,
            'name' => $employee->name,
            'role' => $employee->role,
            'contract_ends_at' => $employee->contract_ends_at?->format('d/m/Y'),
         ])->values()->all();

      return [
         ...$summary,
         'today_attendance' => [
            'present' => (int) ($attendanceCounts['present'] ?? 0),
            'late' => (int) ($attendanceCounts['late'] ?? 0),
            'izin' => (int) ($attendanceCounts['incomplete'] ?? 0),
            'absent' => (int) ($attendanceCounts['absent'] ?? 0),
            'on_duty' => $onDuty,
         ],
         'pending_approvals' => $pendingApprovals,
         'payroll_projection' => [
            'basic' => (int) ($payrollProjection?->basic ?? 0),
            'commission' => (int) ($payrollProjection?->commission ?? 0),
            'overtime' => (int) ($payrollProjection?->overtime ?? 0),
         ],
         'top_performers' => $topPerformers,
         'contract_expiring_soon' => $contractExpiringSoon,
      ];
   }
}
