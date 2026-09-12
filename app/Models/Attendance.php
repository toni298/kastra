<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use App\Models\Shift;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
   use UsesUuid;

   protected $fillable = ['company_id', 'employee_id', 'shift_id', 'attendance_date', 'clock_in', 'clock_out', 'late_minutes', 'status', 'notes', 'adjusted_by'];

   protected function casts(): array
   {
      return ['attendance_date' => 'date', 'clock_in' => 'datetime', 'clock_out' => 'datetime', 'late_minutes' => 'integer'];
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
