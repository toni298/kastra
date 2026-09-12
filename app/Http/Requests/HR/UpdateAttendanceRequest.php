<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{
   public function authorize(): bool
   {
      return $this->user()?->can('hr.attendance.edit') ?? false;
   }

   public function rules(): array
   {
      return [
         'clock_in' => ['nullable', 'date'],
         'clock_out' => ['nullable', 'date', 'after_or_equal:clock_in'],
         'status' => ['required', 'in:present,late,absent,incomplete'],
         'late_minutes' => ['nullable', 'integer', 'min:0'],
         'notes' => ['nullable', 'string', 'max:1000'],
      ];
   }
}
