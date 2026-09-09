<?php

namespace App\Http\Requests\Purchases;

use Illuminate\Foundation\Http\FormRequest;

class IndexPurchaseReturnRequest extends FormRequest
{
   public function authorize(): bool
   {
      return $this->user()?->can('pembelian.returns.view') ?? false;
   }
   public function rules(): array
   {
      return ['search' => ['nullable', 'string', 'max:100'], 'per_page' => ['nullable', 'integer', 'in:10,25,50,100'], 'cursor' => ['nullable', 'string']];
   }
}
