<?php

namespace App\Http\Requests\Purchases;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePurchasePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('pembelian.pay');
    }

    public function rules(): array
    {
        return [
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'integer', 'min:0'],
            'owner_amount' => ['nullable', 'integer', 'min:0'],
            'method' => ['required', Rule::in(['transfer_bank', 'tunai', 'giro'])],
            'reference' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
            'proof_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $companyAmount = (int) ($this->input('amount') ?? 0);
                $ownerAmount = (int) ($this->input('owner_amount') ?? 0);

                if ($companyAmount === 0 && $ownerAmount === 0) {
                    $validator->errors()->add('amount', 'Total pembayaran harus lebih dari 0.');
                }
            },
        ];
    }
}
