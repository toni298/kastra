<?php

namespace App\Http\Requests\Inventory;

use App\Models\ProductStock;
use App\Services\InertiaAuthorizationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexProductStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && app(InertiaAuthorizationService::class)->allows($this->user(), 'inventory.stock.view');
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'gudang_id' => [
                'nullable',
                'uuid',
                Rule::exists('gudang', 'id')->where('company_id', $this->user()->company_id),
            ],
            'category_id' => [
                'nullable',
                'uuid',
                Rule::exists('product_categories', 'id')->where('company_id', $this->user()->company_id),
            ],
            'status' => ['nullable', Rule::in(['safe', 'low', 'out'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50, 100])],
            'sort' => ['nullable', Rule::in(['product_name', 'sku', 'quantity', 'created_at'])],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ];
    }
}
