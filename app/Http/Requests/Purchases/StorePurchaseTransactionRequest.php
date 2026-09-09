<?php

namespace App\Http\Requests\Purchases;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseTransactionRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->can('pembelian.create'); }

    public function rules(): array
    {
        $totalPayment = (int) $this->input('payment_amount', 0) + (int) $this->input('owner_amount', 0);
        $isUnpaid = $totalPayment < 1 || in_array($this->input('payment_status'), ['unpaid', 'partial'], true);

        return [
            'supplier_id' => ['nullable', 'uuid', Rule::exists('suppliers', 'id')->where('company_id', $this->user()->company_id)],
            'gudang_id' => ['nullable', 'uuid', Rule::exists('gudang', 'id')->where('company_id', $this->user()->company_id)],
            'branch_id' => ['nullable', 'uuid', Rule::exists('branches', 'id')->where('company_id', $this->user()->company_id)],
            'document_type' => ['required', Rule::in(['purchase_order', 'purchase_invoice'])],
            'status' => ['required', Rule::in(['draft', 'ordered', 'received', 'completed'])],
            'transaction_date' => ['required', 'date'],
            'due_date' => [$isUnpaid ? 'required' : 'nullable', 'date', 'after_or_equal:transaction_date'],
            'discount' => ['nullable', 'integer', 'min:0'],
            'tax' => ['nullable', 'integer', 'min:0'],
            'payment_method' => ['nullable', Rule::requiredIf(fn () => (int) $this->input('payment_amount', 0) > 0), Rule::in(['transfer_bank', 'tunai', 'giro'])],
            'payment_amount' => ['nullable', 'integer', 'min:0'],
            'owner_amount' => ['nullable', 'integer', 'min:0'],
            'proof_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'note' => ['nullable', 'string', 'max:1000'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.product_id' => ['required', 'uuid', 'distinct', Rule::exists('products', 'id')->where('company_id', $this->user()->company_id)],
            'details.*.quantity' => ['required', 'integer', 'min:1'],
            'details.*.unit_price' => ['required', 'integer', 'min:0'],
            'details.*.discount' => ['nullable', 'integer', 'min:0'],
        ];
    }
}

