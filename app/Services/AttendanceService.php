<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AttendanceService
{
   public function clock(string $companyId, string $pin): array
   {
      $employee = Employee::query()->where('company_id', $companyId)->where('status', 'active')->get()->first(fn($item) => $item->pin && Hash::check($pin, $item->pin));
      abort_if($employee === null, 422, 'PIN tidak ditemukan. Silakan coba lagi.');

      return DB::transaction(function () use ($employee): array {
         $now = now();
         $attendance = Attendance::query()->where('employee_id', $employee->id)->whereDate('attendance_date', $now->toDateString())->lockForUpdate()->first();

         if ($attendance?->clock_in && ! $attendance->clock_out) {
            $attendance->update(['clock_out' => $now, 'status' => 'present']);
            return ['employee_name' => $employee->name, 'action' => 'clock_out', 'time' => $now->format('H:i'), 'status' => 'Tepat Waktu', 'message' => "Sampai jumpa, {$employee->name}. Jam pulang berhasil dicatat."];
         }

         $shift = $employee->shift;
         $lateMinutes = 0;
         if ($shift?->start_time) {
            $scheduledAt = Carbon::parse($now->toDateString() . ' ' . $shift->start_time);
            $elapsedSeconds = max(0, $now->getTimestamp() - $scheduledAt->getTimestamp());
            $lateMinutes = max(0, intdiv($elapsedSeconds, 60) - (int) $shift->grace_period_minutes);
         }
         $status = $lateMinutes > 0 ? 'late' : 'present';
         $attendance = Attendance::updateOrCreate(['employee_id' => $employee->id, 'attendance_date' => $now->toDateString()], ['company_id' => $employee->company_id, 'shift_id' => $shift?->id, 'clock_in' => $now, 'late_minutes' => $lateMinutes, 'status' => $status]);

         return ['employee_name' => $employee->name, 'action' => 'clock_in', 'time' => $now->format('H:i'), 'status' => $status === 'late' ? 'Terlambat' : 'Tepat Waktu', 'message' => "Selamat datang, {$employee->name}. Selamat bekerja!"];
      });
   }

   public function update(Attendance $attendance, array $data): Attendance
   {
      $attendance->update([...$data, 'adjusted_by' => Auth::id()]);
      return $attendance->load(['employee:id,name,nik', 'shift:id,name,start_time,end_time']);
   }
}
