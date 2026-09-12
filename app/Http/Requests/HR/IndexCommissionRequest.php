<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class IndexCommissionRequest extends FormRequest
{
   public function authorize(): bool { return $this->user()?->can('hr.commissions.view') ?? false; }
   public function rules(): array
   {
      return ['from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from'], 'search' => ['nullable', 'string', 'max:100'], 'commission_type' => ['nullable', 'in:percentage,per_quantity'], 'per_page' => ['nullable', 'integer', 'min:5', 'max:100'], 'cursor' => ['nullable', 'string']];
   }
}
