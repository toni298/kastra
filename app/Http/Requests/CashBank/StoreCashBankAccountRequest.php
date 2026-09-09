<?php

namespace App\Http\Requests\CashBank;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Services\CompanyContext;
use Illuminate\Validation\Validator;

class StoreCashBankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can($this->isMethod('post') ? 'cash_bank.accounts.create' : 'cash_bank.accounts.edit') ?? false;
    }
    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:100'], 'type' => ['required', Rule::in(['bank', 'e_wallet'])], 'bank_name' => ['nullable', 'string', 'max:100'], 'account_number' => ['nullable', 'string', 'max:100'], 'account_holder' => ['nullable', 'string', 'max:100'], 'currency' => ['nullable', 'string', 'size:3'], 'opening_balance' => ['nullable', 'integer', 'min:0'], 'is_active' => ['nullable', 'boolean'], 'can_receive_money' => ['required', 'boolean'], 'can_send_money' => ['required', 'boolean'], 'is_all_branches' => ['required', 'boolean'], 'branch_id' => ['nullable', 'uuid', Rule::exists('branches', 'id')->where('company_id', app(CompanyContext::class)->id())], 'replace_default' => ['required', 'boolean']];
    }
    public function after(): array
    {
        return [function (Validator $validator): void {
            $data = $this->validated();
            if (! ($data['can_receive_money'] || $data['can_send_money'])) $validator->errors()->add('can_receive_money', 'Pilih minimal Kas Masuk atau Kas Keluar.');
            if (! ($data['is_all_branches'] ?? true) && empty($data['branch_id'])) $validator->errors()->add('branch_id', 'Cabang wajib dipilih jika tidak berlaku untuk semua cabang.');
        }];
    }
}
