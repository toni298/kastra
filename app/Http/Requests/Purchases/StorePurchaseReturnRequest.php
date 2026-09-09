<?php

namespace App\Http\Requests\Purchases;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('pembelian.return');
    }

    public function rules(): array
    {
        return [
            'returnDate' => ['required', 'date'],
            'reason' => ['required', Rule::in(['rusak', 'tidak_sesuai', 'jumlah_lebih', 'kedaluwarsa'])],
            'resolution' => ['required', Rule::in(['penggantian', 'potong_tagihan', 'refund'])],
            'refund_account_id' => ['nullable', 'uuid', Rule::requiredIf(fn() => $this->input('resolution') === 'refund')],
            'note' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
