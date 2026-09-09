<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Contracts\Auth\Access\Gate;

class UpdateProductStockRequest extends StoreProductStockRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && app(Gate::class)->forUser($this->user())->allows('update', $this->route('product_stock'));
    }

    public function rules(): array
    {
        return $this->stockRules($this->route('product_stock')->id);
    }
}
