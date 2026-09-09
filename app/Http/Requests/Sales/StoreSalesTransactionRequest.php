<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSalesTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('penjualan.transactions.create');
    }

    public function rules(): array
    {
        $user = $this->user();
        $isUnpaid = in_array($this->input('payment_status'), ['unpaid', 'partial'], true)
            || (int) $this->input('payment_amount', 0) < 1;

        return [
            'branch_id' => [
                'required',
                'uuid',
                Rule::exists('branches', 'id')
                    ->where('company_id', $user->company_id)
                    ->where('status', 'active')
                    ->when($user->branch_id, fn($rule) => $rule->where('id', $user->branch_id)),
            ],
            'customer_id' => ['nullable', 'uuid', Rule::exists('customers', 'id')->where('company_id', $this->user()->company_id)],
            'document_type' => ['required', Rule::in(['invoice', 'quotation', 'sales_order'])],
            'transaction_date' => ['required', 'date'],
            'due_date' => [$isUnpaid ? 'required' : 'nullable', 'date', 'after_or_equal:transaction_date'],
            'payment_status' => ['required', Rule::in(['unpaid', 'paid'])],
            'status' => ['nullable', Rule::in(['draft', 'completed'])],
            'discount' => ['nullable', 'integer', 'min:0'],
            'tax' => ['nullable', 'integer', 'min:0'],
            'tax_mode' => ['nullable', Rule::in(['inclusive', 'exclusive'])],
            'payment_method' => ['required', Rule::in(['cash', 'transfer', 'qris', 'ewallet'])],
            'payment_amount' => ['required', 'integer', 'min:0'],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'proof_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'payment_note' => ['nullable', 'string', 'max:1000'],
            'note' => ['nullable', 'string', 'max:1000'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.product_id' => ['required', 'uuid', 'distinct', Rule::exists('products', 'id')->where('company_id', $this->user()->company_id)->where('is_active', true)],
            'details.*.quantity' => ['required', 'integer', 'min:1'],
            'details.*.unit_price' => ['required', 'integer', 'min:0'],
        ];
    }
}
