<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AttendanceService
{
   public function clock(string $companyId, array $credentials): array
   {
      $employee = $this->findEmployee($companyId, $credentials);
      if ($employee === null) {
         return $this->rejected(null, now(), 'Identitas karyawan tidak ditemukan atau tidak aktif.');
      }

      return DB::transaction(function () use ($employee, $credentials): array {
         $now = now();
         $latitude = (float) $credentials['latitude'];
         $longitude = (float) $credentials['longitude'];
         $shift = $employee->shift;
         $attendanceDate = $this->attendanceDate($shift, $now);
         $lastScan = $this->lastScan($employee->id, $attendanceDate);

         if ($lastScan && $lastScan->diffInSeconds($now) < (int) config('kiosk.cooldown_seconds')) {
            return $this->rejected($employee, $now, 'Scan ulang ditolak. Silakan tunggu 3 menit sebelum mencoba lagi.');
         }

         $attendance = Attendance::query()
            ->where('employee_id', $employee->id)
            ->whereDate('attendance_date', $attendanceDate->toDateString())
            ->lockForUpdate()
            ->first();

         if ($attendance?->clock_in && $attendance->clock_out) {
            return $this->rejected($employee, $now, 'Absensi Hari Ini Sudah Lengkap.');
         }

         if ($attendance?->clock_in && ! $attendance->clock_out) {
            if ($attendance->clock_in->diffInSeconds($now) < (int) config('kiosk.minimum_work_seconds')) {
               return $this->rejected($employee, $now, 'Jam kerja minimum 15 menit belum terpenuhi.');
            }

            $earlyLeave = $shift && $now->lt($this->scheduledEnd($shift, $attendanceDate));
            $attendance->update(['clock_out' => $now, 'clock_out_latitude' => $latitude, 'clock_out_longitude' => $longitude, 'status' => 'present']);

            return $this->success($employee, $now, 'CLOCK_OUT', $earlyLeave ? 'EARLY_LEAVE' : 'ON_TIME', $earlyLeave
               ? "{$employee->name}, Anda pulang lebih awal."
               : "Sampai jumpa, {$employee->name}. Absen pulang berhasil.");
         }

         $scheduledStart = $shift ? $this->scheduledStart($shift, $attendanceDate) : null;
         $lateMinutes = $scheduledStart && $now->gt($scheduledStart)
            ? max(0, (int) floor($scheduledStart->diffInSeconds($now) / 60) - (int) $shift->grace_period_minutes)
            : 0;
         $attendance = Attendance::updateOrCreate(
            ['employee_id' => $employee->id, 'attendance_date' => $attendanceDate->toDateString()],
            ['company_id' => $employee->company_id, 'shift_id' => $shift?->id, 'clock_in' => $now, 'clock_in_latitude' => $latitude, 'clock_in_longitude' => $longitude, 'late_minutes' => $lateMinutes, 'status' => $lateMinutes > 0 ? 'late' : 'present'],
         );

         return $this->success($employee, $now, 'CLOCK_IN', $lateMinutes > 0 ? 'LATE' : 'ON_TIME', $lateMinutes > 0
            ? "Selamat datang, {$employee->name}. Anda terlambat {$lateMinutes} menit."
            : "Selamat Datang, {$employee->name} - Absen Masuk Berhasil.");
      });
   }

   private function findEmployee(string $companyId, array $credentials): ?Employee
   {
      $query = Employee::query()->where('company_id', $companyId)->where('status', 'active')->with('shift');
      $employee = null;
      if (! empty($credentials['user_id'])) $employee = (clone $query)->whereKey($credentials['user_id'])->first();
      if (! $employee && ! empty($credentials['rfid_code'])) $employee = (clone $query)->where('rfid_code', $credentials['rfid_code'])->first();
      if (! $employee && ! empty($credentials['qr_code_data'])) $employee = (clone $query)->where('qr_code_data', $credentials['qr_code_data'])->first();
      if (! $employee && ! empty($credentials['face_recognition_hash'])) $employee = (clone $query)->where('face_recognition_hash', hash('sha256', $credentials['face_recognition_hash']))->first();
      if (! $employee && ! empty($credentials['pin'])) {
         $employee = (clone $query)->get()->first(fn (Employee $item): bool => $item->pin && Hash::check($credentials['pin'], $item->pin));
      }
      return $employee;
   }

   private function attendanceDate(?\App\Models\Shift $shift, Carbon $now): Carbon
   {
      if ($shift && $shift->end_time <= $shift->start_time && $now->format('H:i:s') < $shift->end_time) return $now->copy()->subDay()->startOfDay();
      return $now->copy()->startOfDay();
   }

   private function scheduledStart(\App\Models\Shift $shift, Carbon $attendanceDate): Carbon
   {
      return $attendanceDate->copy()->setTimeFromTimeString($shift->start_time);
   }

   private function scheduledEnd(\App\Models\Shift $shift, Carbon $attendanceDate): Carbon
   {
      $end = $attendanceDate->copy()->setTimeFromTimeString($shift->end_time);
      return $shift->end_time <= $shift->start_time ? $end->addDay() : $end;
   }

   private function lastScan(string $employeeId, Carbon $attendanceDate): ?Carbon
   {
      $records = Attendance::query()->where('employee_id', $employeeId)->whereDate('attendance_date', '>=', $attendanceDate->copy()->subDay())->latest('attendance_date')->limit(2)->get(['clock_in', 'clock_out']);
      return $records->flatMap(fn (Attendance $attendance): Collection => collect([$attendance->clock_in, $attendance->clock_out]))->filter()->sortDesc()->first();
   }

   private function success(Employee $employee, Carbon $now, string $actionType, string $attendanceStatus, string $message): array
   {
      return ['status' => 'success', 'action_type' => $actionType, 'user_info' => ['id' => $employee->id, 'name' => $employee->name, 'photo' => null, 'role' => $employee->role, 'department' => null], 'timestamp' => $now->toIso8601String(), 'attendance_status' => $attendanceStatus, 'ui_message' => $message, 'sound_instruction' => 'success', 'action' => strtolower($actionType), 'employee_name' => $employee->name, 'time' => $now->format('H:i'), 'status_label' => $attendanceStatus];
   }

   private function rejected(?Employee $employee, Carbon $now, string $message): array
   {
      return ['status' => 'error', 'action_type' => 'REJECTED', 'user_info' => $employee ? ['id' => $employee->id, 'name' => $employee->name, 'photo' => null, 'role' => $employee->role, 'department' => null] : null, 'timestamp' => $now->toIso8601String(), 'attendance_status' => null, 'ui_message' => $message, 'sound_instruction' => 'error', 'action' => 'rejected', 'employee_name' => $employee?->name, 'time' => $now->format('H:i'), 'status_label' => 'REJECTED'];
   }

   public function update(Attendance $attendance, array $data): Attendance
   {
      $attendance->update([...$data, 'adjusted_by' => Auth::id()]);
      return $attendance->load(['employee:id,name,nik', 'shift:id,name,start_time,end_time']);
   }
}
