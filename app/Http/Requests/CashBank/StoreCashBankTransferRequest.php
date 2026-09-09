<?php

namespace App\Http\Requests\CashBank;

use Illuminate\Foundation\Http\FormRequest;

class StoreCashBankTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('cash_bank.transfers.create') ?? false;
    }
    public function rules(): array
    {
        return ['source_account_id' => ['required', 'uuid', 'different:destination_account_id'], 'destination_account_id' => ['required', 'uuid'], 'amount' => ['required', 'integer', 'min:1'], 'transfer_date' => ['required', 'date'], 'reference' => ['nullable', 'string', 'max:100'], 'note' => ['nullable', 'string', 'max:1000']];
    }
}
