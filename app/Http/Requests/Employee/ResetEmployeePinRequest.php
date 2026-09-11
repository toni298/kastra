<?php

namespace App\Http\Requests\Employee;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;

class ResetEmployeePinRequest extends FormRequest
{
   public function authorize(): bool
   {
      return $this->user()->can('update', $this->route('employee'));
   }

   public function rules(): array
   {
      return ['pin' => ['required', 'digits:6']];
   }
}
