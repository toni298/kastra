<?php

namespace App\Http\Requests\Inventory;

use App\Services\InertiaAuthorizationService;
use Illuminate\Foundation\Http\FormRequest;

class StoreStockAdjustmentRequest extends FormRequest
{
   public function authorize(): bool
   {
      return $this->user() !== null
         && app(InertiaAuthorizationService::class)->allows($this->user(), 'inventory.summary.adjust');
   }

   public function rules(): array
   {
      return [
         'branch_id' => ['required', 'uuid', 'exists:branches,id'],
         'product_id' => ['required', 'uuid', 'exists:products,id'],
         'type' => ['required', 'in:in,out'],
         'quantity' => ['required', 'integer', 'min:1'],
         'note' => ['nullable', 'string', 'max:500'],
      ];
   }

   public function messages(): array
   {
      return [
         'branch_id.required' => 'Cabang wajib dipilih.',
         'product_id.required' => 'Produk wajib dipilih.',
         'type.required' => 'Jenis penyesuaian wajib dipilih.',
         'quantity.required' => 'Jumlah wajib diisi.',
         'quantity.min' => 'Jumlah minimal 1.',
      ];
   }
}
