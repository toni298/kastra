<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use App\Models\Shift;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
   use UsesUuid;

   protected $fillable = ['company_id', 'employee_id', 'shift_id', 'attendance_date', 'clock_in', 'clock_in_latitude', 'clock_in_longitude', 'clock_out', 'clock_out_latitude', 'clock_out_longitude', 'late_minutes', 'status', 'notes', 'adjusted_by'];

   protected function casts(): array
   {
      return ['attendance_date' => 'date', 'clock_in' => 'datetime', 'clock_in_latitude' => 'decimal:7', 'clock_in_longitude' => 'decimal:7', 'clock_out' => 'datetime', 'clock_out_latitude' => 'decimal:7', 'clock_out_longitude' => 'decimal:7', 'late_minutes' => 'integer'];
   }

   public function employee(): BelongsTo
   {
      return $this->belongsTo(Employee::class);
   }

   public function shift(): BelongsTo
   {
      return $this->belongsTo(Shift::class);
   }
}
