<?php

namespace App\Services;

use App\Models\CashBankAccountSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashBankAccountSettingService
{
    public function create(string $companyId, array $data): CashBankAccountSetting
    {
        return DB::transaction(function () use ($companyId, $data): CashBankAccountSetting {
            $this->lockScope($companyId, $data['branch_id'] ?? null);
            $this->ensureUniqueConfiguration($companyId, $data['cash_bank_account_id'], $data['branch_id'] ?? null);
            $setting = CashBankAccountSetting::create([...$data, 'company_id' => $companyId]);
            $this->clearConflictingDefaults($setting, $data);

            return $setting->refresh();
        });
    }

    public function update(CashBankAccountSetting $setting, array $data): CashBankAccountSetting
    {
        return DB::transaction(function () use ($setting, $data): CashBankAccountSetting {
            $this->lockScope($setting->company_id, $data['branch_id'] ?? null);
            $this->ensureUniqueConfiguration($setting->company_id, $data['cash_bank_account_id'], $data['branch_id'] ?? null, $setting->id);
            $setting->update($data);
            $this->clearConflictingDefaults($setting, $data);

            return $setting->refresh();
        });
    }

    public function delete(CashBankAccountSetting $setting): void
    {
        DB::transaction(fn () => $setting->delete());
    }

    private function clearConflictingDefaults(CashBankAccountSetting $setting, array $data): void
    {
        $scope = CashBankAccountSetting::query()
            ->where('company_id', $setting->company_id)
            ->whereKeyNot($setting->id);

        $setting->branch_id ? $scope->where('branch_id', $setting->branch_id) : $scope->whereNull('branch_id');

        if ($data['is_default_receive']) {
            (clone $scope)->where('is_default_receive', true)->update(['is_default_receive' => false]);
        }

        if ($data['is_default_payment']) {
            (clone $scope)->where('is_default_payment', true)->update(['is_default_payment' => false]);
        }
    }

    private function ensureUniqueConfiguration(string $companyId, string $accountId, ?string $branchId, ?string $exceptId = null): void
    {
        $query = CashBankAccountSetting::query()
            ->where('company_id', $companyId)
            ->where('cash_bank_account_id', $accountId)
            ->lockForUpdate();

        $branchId ? $query->where('branch_id', $branchId) : $query->whereNull('branch_id');

        if ($exceptId) {
            $query->whereKeyNot($exceptId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'cash_bank_account_id' => 'Konfigurasi rekening untuk cabang ini sudah ada.',
            ]);
        }
    }

    private function lockScope(string $companyId, ?string $branchId): void
    {
        $query = CashBankAccountSetting::query()->where('company_id', $companyId)->lockForUpdate();

        $branchId ? $query->where('branch_id', $branchId) : $query->whereNull('branch_id');

        $query->get(['id']);
    }
}
