<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->can('products.create');
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'category_id' => [
                'nullable',
                'uuid',
                Rule::exists('product_categories', 'id')->where('company_id', $companyId),
            ],
            'brand_id' => [
                'nullable',
                'uuid',
                Rule::exists('product_brands', 'id')->where('company_id', $companyId),
            ],
            'unit_id' => [
                'required',
                'uuid',
                Rule::exists('units', 'id')->where('company_id', $companyId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'required',
                'string',
                'max:80',
                Rule::unique('products', 'sku')->where('company_id', $companyId),
            ],
            'barcode' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'barcode')->where('company_id', $companyId),
            ],
            'purchase_price' => ['required', 'integer', 'min:0'],
            'selling_price' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'branch_stocks' => ['nullable', 'array'],
            'branch_stocks.*.branch_id' => [
                'required',
                'uuid',
                Rule::exists('branches', 'id')->where('company_id', $companyId),
            ],
            'branch_stocks.*.quantity' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:10000'],
            'is_active' => ['required', 'boolean'],
            'image_ids' => ['array', 'max:10'],
            'image_ids.*' => [
                'uuid',
                'distinct',
                Rule::exists('product_images', 'id')
                    ->where('company_id', $companyId)
                    ->where('uploaded_by', $this->user()->getKey())
                    ->whereNull('product_id'),
            ],
            'images' => [
                'array',
                'max:10',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_array($value)) {
                        return;
                    }

                    $imageIds = $this->input('image_ids', []);

                    if (count($value) + count(is_array($imageIds) ? $imageIds : []) > 10) {
                        $fail('Maksimal 10 foto dapat disimpan untuk setiap produk.');
                    }
                },
            ],
            'images.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
