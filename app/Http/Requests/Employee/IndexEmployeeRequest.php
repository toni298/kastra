<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexEmployeeRequest extends FormRequest
{
   public function authorize(): bool
   {
      return $this->user()->can('hr.employees.view');
   }

   public function rules(): array
   {
      return [
         'search' => ['nullable', 'string', 'max:100'],
         'role' => ['nullable', Rule::in(['cashier', 'supervisor', 'staff'])],
         'status' => ['nullable', Rule::in(['active', 'inactive'])],
         'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
         'cursor' => ['nullable', 'string'],
         'tab' => ['nullable', Rule::in(['summary', 'employees', 'attendance', 'commission', 'payroll'])],
      ];
   }
}
