<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class StoreShiftRequest extends FormRequest
{
   public function authorize(): bool
   {
      return $this->user()?->can('hr.shifts.manage') ?? false;
   }

   public function rules(): array
   {
      return [
         'name' => ['required', 'string', 'max:100'],
         'start_time' => ['required', 'date_format:H:i'],
         'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
         'grace_period_minutes' => ['required', 'integer', 'min:0', 'max:180'],
         'is_active' => ['sometimes', 'boolean'],
      ];
   }
}
