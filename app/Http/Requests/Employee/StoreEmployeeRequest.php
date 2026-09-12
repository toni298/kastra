<?php

namespace App\Http\Requests\Employee;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
   public function authorize(): bool
   {
      return $this->isMethod('post')
         ? $this->user()->can('create', Employee::class)
         : $this->user()->can('update', $this->route('employee'));
   }

   public function rules(): array
   {
      $employeeId = $this->route('employee')?->id;

      return [
         'branch_id' => ['nullable', 'uuid', Rule::exists('branches', 'id')->where('company_id', $this->user()->company_id)],
         'shift_id' => ['nullable', 'uuid', Rule::exists('shifts', 'id')->where('company_id', $this->user()->company_id)],
         'nik' => ['required', 'string', 'max:50', Rule::unique('employees', 'nik')->where('company_id', $this->user()->company_id)->ignore($employeeId)],
         'name' => ['required', 'string', 'max:255'],
         'phone' => ['nullable', 'string', 'max:32'],
         'email' => ['nullable', 'email', 'max:255'],
         'address' => ['nullable', 'string', 'max:1000'],
         'role' => ['required', Rule::in(['cashier', 'supervisor', 'staff'])],
         'base_salary' => ['required', 'integer', 'min:0'],
         'allowance' => ['nullable', 'integer', 'min:0'],
         'commission_type' => ['required', Rule::in(['percentage', 'per_quantity'])],
         'commission_value' => ['required', 'numeric', 'min:0'],
         'status' => ['required', Rule::in(['active', 'inactive'])],
         'hired_at' => ['nullable', 'date'],
         'pin' => [$this->isMethod('post') ? 'required' : 'nullable', 'nullable', 'digits:6'],
      ];
   }
}
