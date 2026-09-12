<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class IndexOvertimeRequest extends FormRequest
{
   public function authorize(): bool { return $this->user()?->can('hr.overtime.view') ?? false; }
   public function rules(): array
   {
      return ['from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from'], 'status' => ['nullable', 'in:pending,approved,rejected'], 'search' => ['nullable', 'string', 'max:100'], 'per_page' => ['nullable', 'integer', 'min:5', 'max:100'], 'cursor' => ['nullable', 'string']];
   }
}
