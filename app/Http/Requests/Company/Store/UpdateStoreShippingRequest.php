<?php

namespace App\Http\Requests\Company\Store;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreShippingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('store.settings.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'pickup_enabled' => ['nullable', 'boolean'],
            'delivery_enabled' => ['nullable', 'boolean'],
            'flat_shipping_cost' => ['nullable', 'integer', 'min:0'],
            'free_shipping_min' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
