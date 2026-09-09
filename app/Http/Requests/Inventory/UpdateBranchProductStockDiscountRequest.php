<?php

namespace App\Http\Requests\Inventory;

use App\Services\InertiaAuthorizationService;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBranchProductStockDiscountRequest extends FormRequest
{
   public function authorize(): bool
   {
      return $this->user() !== null
         && app(InertiaAuthorizationService::class)->allows($this->user(), 'inventory.summary.discount');
   }

   public function rules(): array
   {
      return [
         'discount' => ['required', 'integer', 'min:0', 'max:100'],
      ];
   }
}
