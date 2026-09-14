<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
   public function toArray(Request $request): array
   {
      return [
         'id' => $this->id,
         'attendance_date' => $this->attendance_date?->toDateString(),
         'employee' => [
            'id' => $this->employee?->id,
            'name' => $this->employee?->name,
            'nik' => $this->employee?->nik,
         ],
         'shift' => $this->shift ? [
            'id' => $this->shift->id,
            'name' => $this->shift->name,
            'start_time' => substr($this->shift->start_time, 0, 5),
            'end_time' => substr($this->shift->end_time, 0, 5),
         ] : null,
         'clock_in' => $this->clock_in?->format('H:i'),
         'clock_in_location' => $this->location($this->clock_in_latitude, $this->clock_in_longitude),
         'clock_out' => $this->clock_out?->format('H:i'),
         'clock_out_location' => $this->location($this->clock_out_latitude, $this->clock_out_longitude),
         'late_minutes' => $this->late_minutes,
         'status' => $this->status,
         'notes' => $this->notes,
      ];
   }

   private function location($latitude, $longitude): ?array
   {
      if ($latitude === null || $longitude === null) return null;

      return ['latitude' => (float) $latitude, 'longitude' => (float) $longitude];
   }
}
