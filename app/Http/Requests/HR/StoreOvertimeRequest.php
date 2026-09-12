<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOvertimeRequest extends FormRequest
{
   public function authorize(): bool { return $this->user()?->can('hr.overtime.create') ?? false; }
   public function rules(): array
   {
      return ['employee_id' => ['required', 'uuid', Rule::exists('employees', 'id')->where('company_id', $this->user()->company_id)], 'overtime_date' => ['required', 'date'], 'hours' => ['required', 'numeric', 'min:0.25', 'max:24'], 'hourly_rate' => ['required', 'integer', 'min:0'], 'reason' => ['required', 'string', 'max:1000'], 'source' => ['sometimes', 'in:manual,automatic']];
   }
}
