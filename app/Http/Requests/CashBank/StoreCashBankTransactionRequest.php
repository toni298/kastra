<?php

namespace App\Http\Requests\CashBank;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCashBankTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can($this->isMethod('post') ? 'cash_bank.transactions.create' : 'cash_bank.transactions.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'cash_bank_account_id' => ['nullable', 'uuid', 'exists:cash_bank_accounts,id', 'required_unless:type,none'],
            'type' => ['required', Rule::in(['in', 'out', 'none'])],
            'category' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'integer', 'min:1'],
            'transaction_date' => ['required', 'date'],
            'proof_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'proof_file_removed' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
