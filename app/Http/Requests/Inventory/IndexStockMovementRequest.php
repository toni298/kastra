<?php

namespace App\Http\Requests\Inventory;

use App\Services\InertiaAuthorizationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexStockMovementRequest extends FormRequest
{
   public function authorize(): bool
   {
      return $this->user() !== null
         && app(InertiaAuthorizationService::class)->allows($this->user(), 'inventory.movements.view');
   }

   public function rules(): array
   {
      return [
         'product_id' => ['nullable', 'uuid', Rule::exists('products', 'id')->where('company_id', $this->user()->company_id)],
            'branch_id' => ['nullable', 'uuid'],
            'gudang_id' => ['nullable', 'uuid'],
            'user_id' => ['nullable', 'uuid'],
         'movement_type' => ['nullable', 'string', Rule::in(['SALE', 'PURCHASE', 'OPNAME_ADJUSTMENT', 'VOID_SALE', 'RETUR_CUSTOMER', 'RETUR_SUPPLIER', 'DAMAGE', 'TRANSFER_IN', 'TRANSFER_OUT', 'in', 'out', 'adjustment'])],
         'date_from' => ['nullable', 'date'],
         'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
         'search' => ['nullable', 'string', 'max:100'],
         'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50, 100])],
      ];
   }
}
