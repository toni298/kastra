<?php

namespace App\Http\Requests\CashBank;

use App\Models\CashBankAccountSetting;
use App\Services\CompanyContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCashBankAccountSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $companyId = app(CompanyContext::class)->id();

        return [
            'cash_bank_account_id' => ['required', 'uuid', Rule::exists('cash_bank_accounts', 'id')->where('company_id', $companyId)],
            'branch_id' => ['nullable', 'uuid', Rule::exists('branches', 'id')->where('company_id', $companyId)],
            'can_receive_money' => ['required', 'boolean'],
            'can_send_money' => ['required', 'boolean'],
            'is_default_receive' => ['required', 'boolean'],
            'is_default_payment' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $data = $this->validated();

            if (($data['can_receive_money'] ?? false) || ($data['can_send_money'] ?? false)) {
                $this->validateDefaults($validator, $data);
                $this->validateDuplicate($validator, $data);

                return;
            }

            $validator->errors()->add('can_receive_money', 'Minimal salah satu jenis transaksi harus diizinkan.');
        }];
    }

    private function validateDefaults(Validator $validator, array $data): void
    {
        if (($data['is_default_receive'] ?? false) && ! ($data['can_receive_money'] ?? false)) {
            $validator->errors()->add('is_default_receive', 'Default kas masuk harus dapat menerima uang.');
        }

        if (($data['is_default_payment'] ?? false) && ! ($data['can_send_money'] ?? false)) {
            $validator->errors()->add('is_default_payment', 'Default kas keluar harus dapat mengirim uang.');
        }

        if (! ($data['is_active'] ?? false) && (($data['is_default_receive'] ?? false) || ($data['is_default_payment'] ?? false))) {
            $validator->errors()->add('is_active', 'Rekening default harus aktif.');
        }
    }

    private function validateDuplicate(Validator $validator, array $data): void
    {
        $query = CashBankAccountSetting::query()
            ->where('company_id', app(CompanyContext::class)->id())
            ->where('cash_bank_account_id', $data['cash_bank_account_id']);

        $data['branch_id'] ?? null ? $query->where('branch_id', $data['branch_id']) : $query->whereNull('branch_id');

        $setting = $this->route('account_setting');
        if ($setting) {
            $query->whereKeyNot($setting->getKey());
        }

        if ($query->exists()) {
            $validator->errors()->add('cash_bank_account_id', 'Konfigurasi rekening untuk cabang ini sudah ada.');
        }
    }
}
