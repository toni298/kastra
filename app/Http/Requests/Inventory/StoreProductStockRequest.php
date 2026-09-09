<?php

namespace App\Http\Requests\Inventory;

use App\Models\Gudang;
use App\Models\ProductStock;
use App\Services\InertiaAuthorizationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && app(InertiaAuthorizationService::class)->allows($this->user(), 'inventory.stock.create');
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('gudang_id')) {
            return;
        }

        $companyId = $this->user()->company_id;
        $warehouseIds = Gudang::query()
            ->where('company_id', $companyId)
            ->where('aktif', true)
            ->pluck('id');

        if ($warehouseIds->count() === 1) {
            $this->merge(['gudang_id' => $warehouseIds->first()]);
        }
    }

    public function rules(): array
    {
        return $this->stockRules();
    }

    protected function stockRules(?string $ignoreId = null): array
    {
        $companyId = $this->user()->company_id;

        return [
            'product_id' => [
                'required',
                'uuid',
                Rule::exists('products', 'id')->where('company_id', $companyId)->where('is_active', true),
            ],
            'gudang_id' => [
                'required',
                'uuid',
                Rule::exists('gudang', 'id')->where('company_id', $companyId),
                Rule::unique('product_stocks', 'gudang_id')
                    ->where('company_id', $companyId)
                    ->where('product_id', $this->input('product_id'))
                    ->ignore($ignoreId),
            ],
            'quantity' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return ['gudang_id.unique' => 'Maaf produk ini sudah terdaftar di gudang pilihan anda.'];
    }
}
