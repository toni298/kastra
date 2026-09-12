<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class IndexPayrollRequest extends FormRequest
{
   public function authorize(): bool { return $this->user()?->can('hr.payroll.view') ?? false; }
   public function rules(): array { return ['period' => ['nullable', 'date_format:Y-m'], 'status' => ['nullable', 'in:draft,approved,posted'], 'search' => ['nullable', 'string', 'max:100'], 'per_page' => ['nullable', 'integer', 'min:5', 'max:100'], 'cursor' => ['nullable', 'string']]; }
}
