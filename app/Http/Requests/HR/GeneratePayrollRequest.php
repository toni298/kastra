<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class GeneratePayrollRequest extends FormRequest
{
   public function authorize(): bool { return $this->user()?->can('hr.payroll.generate') ?? false; }
   public function rules(): array { return ['period' => ['required', 'date_format:Y-m'], 'cutoff_date' => ['required', 'date']]; }
}
