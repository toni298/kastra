<?php

namespace App\Http\Requests\Product;

use Illuminate\Validation\Rule;

class UpdateProductRequest extends StoreProductRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && app(\Illuminate\Contracts\Auth\Access\Gate::class)
            ->forUser($user)
            ->allows('update', $this->route('product'));
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $productId = $this->route('product')->id;
        $companyId = $this->user()->company_id;

        $rules['sku'] = [
            'required',
            'string',
            'max:80',
            Rule::unique('products', 'sku')->where('company_id', $companyId)->ignore($productId),
        ];
        $rules['barcode'] = [
            'nullable',
            'string',
            'max:100',
            Rule::unique('products', 'barcode')->where('company_id', $companyId)->ignore($productId),
        ];
        $rules['image_ids.*'] = [
            'uuid',
            'distinct',
            Rule::exists('product_images', 'id')
                ->where('company_id', $companyId)
                ->where('product_id', $productId),
        ];

        return $rules;
    }
}
