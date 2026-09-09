<?php

namespace App\Http\Requests\CashBank;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexCashBankTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 20, 50, 100])],
            'sort' => ['nullable', Rule::in(['transfer_number', 'transfer_date', 'amount'])],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'cursor' => ['nullable', 'string'],
        ];
    }
}
