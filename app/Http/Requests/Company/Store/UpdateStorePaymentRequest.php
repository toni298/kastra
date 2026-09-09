<?php

namespace App\Http\Requests\Company\Store;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('store.settings.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'payment_cod_enabled' => ['nullable', 'boolean'],
            'payment_transfer_enabled' => ['nullable', 'boolean'],
            'payment_qris_enabled' => ['nullable', 'boolean'],
            'payment_notes' => ['nullable', 'string', 'max:2000'],
            'qris_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_qris_image' => ['nullable', 'boolean'],
            'bank_accounts' => ['nullable', 'array', 'max:10'],
            'bank_accounts.*.bank_name' => ['required_with:bank_accounts', 'string', 'max:50'],
            'bank_accounts.*.account_number' => ['required_with:bank_accounts', 'string', 'max:50'],
            'bank_accounts.*.account_name' => ['nullable', 'string', 'max:100'],
            'bank_accounts.*.is_active' => ['nullable', 'boolean'],
        ];
    }
}
