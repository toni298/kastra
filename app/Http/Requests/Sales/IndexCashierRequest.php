<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexCashierRequest extends FormRequest
{
   public function authorize(): bool
   {
      return $this->user()?->can('cashier.access') ?? false;
   }

   public function rules(): array
   {
      $user = $this->user();

      return [
         'search' => ['nullable', 'string', 'max:100'],
         'category_id' => [
            'nullable',
            Rule::when(
               $this->filled('category_id') && $this->input('category_id') !== 'other',
               [
                  'uuid',
                  Rule::exists('product_categories', 'id')
                     ->where('company_id', $user->company_id)
                     ->where('is_active', true),
               ],
            ),
         ],
         'branch_id' => [
            'nullable',
            'uuid',
            Rule::exists('branches', 'id')
               ->where('company_id', $user->company_id)
               ->where('status', 'active')
               ->when($user->branch_id, fn($rule) => $rule->where('id', $user->branch_id)),
         ],
         'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
      ];
   }
}
