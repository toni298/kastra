<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class IndexAttendanceRequest extends FormRequest
{
   public function authorize(): bool
   {
      return $this->user()?->can('hr.attendance.view') ?? false;
   }

   public function rules(): array
   {
      return [
         'date' => ['nullable', 'date'],
         'status' => ['nullable', 'in:present,late,absent,incomplete'],
         'search' => ['nullable', 'string', 'max:100'],
         'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
         'cursor' => ['nullable', 'string'],
      ];
   }
}
