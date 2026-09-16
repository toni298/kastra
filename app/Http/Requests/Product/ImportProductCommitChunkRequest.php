<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ImportProductCommitChunkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('products.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'rows' => ['required', 'array'],
            'rows.*.name' => ['required', 'string', 'max:255'],
            'rows.*.sku' => ['required', 'string', 'max:80'],
            'rows.*.barcode' => ['nullable', 'string', 'max:100'],
            'rows.*.category' => ['nullable', 'string', 'max:255'],
            'rows.*.brand' => ['nullable', 'string', 'max:255'],
            'rows.*.unit' => ['nullable', 'string', 'max:100'],
            'rows.*.category_id' => ['nullable', 'uuid'],
            'rows.*.brand_id' => ['nullable', 'uuid'],
            'rows.*.unit_id' => ['nullable', 'uuid'],
            'rows.*.purchase_price' => ['required', 'integer', 'min:0'],
            'rows.*.selling_price' => ['required', 'integer', 'min:0'],
            'rows.*.minimum_stock' => ['required', 'integer', 'min:0'],
            'rows.*.initial_stock' => ['nullable', 'integer', 'min:0'],
            'rows.*.description' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
