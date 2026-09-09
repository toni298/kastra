<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSalesReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('penjualan.transactions.return');
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['draft', 'completed'])],
            'reason' => ['required', Rule::in(['damaged', 'wrong_delivery', 'not_as_ordered', 'expired', 'other'])],
            'resolution' => ['required', Rule::in(['refund', 'ganti_produk', 'potong_tagihan'])],
            'note' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'replacements' => ['nullable', 'array'],
            'replacements.*.product_id' => ['required', 'uuid', 'distinct'],
            'replacements.*.quantity' => ['required', 'integer', 'min:1'],
            'replacements.*.unit_price' => ['required', 'integer', 'min:0'],
            'settlement' => ['nullable', Rule::in(['refund', 'potong_tagihan'])],
        ];
    }
}
