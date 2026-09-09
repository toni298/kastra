<?php

namespace App\Services;

use App\Models\CashBankAccount;
use App\Models\CashBankAccountSetting;
use Illuminate\Support\Facades\DB;

class CashBankAccountService
{
    public function __construct(private CashBankAccountSettingService $settings) {}

    public function create(string $companyId, array $data): CashBankAccount
    {
        return DB::transaction(function () use ($companyId, $data): CashBankAccount {
            $account = CashBankAccount::create([
                ...$this->accountData($data),
                'company_id' => $companyId,
                'current_balance' => $data['opening_balance'] ?? 0,
            ]);
            $this->saveSetting($companyId, $account, $data);

            return $account;
        });
    }

    public function update(CashBankAccount $account, array $data): CashBankAccount
    {
        return DB::transaction(function () use ($account, $data): CashBankAccount {
            $openingBalance = (int) ($data['opening_balance'] ?? $account->opening_balance);
            $account->update([
                ...$this->accountData($data),
                'current_balance' => $account->current_balance + ($openingBalance - $account->opening_balance),
            ]);

            if ($account->type !== 'cash') {
                $isAllBranches = $data['is_all_branches'] ?? true;
                $newBranchId = $isAllBranches ? null : ($data['branch_id'] ?? null);
                CashBankAccountSetting::query()
                    ->where('cash_bank_account_id', $account->id)
                    ->when($newBranchId, fn ($query) => $query->where('branch_id', '!=', $newBranchId)->orWhereNull('branch_id'))
                    ->when(! $newBranchId, fn ($query) => $query->whereNotNull('branch_id'))
                    ->delete();
                $this->saveSetting((string) $account->company_id, $account, $data);
            }

            return $account->refresh();
        });
    }

    private function saveSetting(string $companyId, CashBankAccount $account, array $data): void
    {
        if ($account->type === 'cash') {
            return;
        }

        $isAllBranches = $data['is_all_branches'] ?? true;
        $branchId = $isAllBranches ? null : ($data['branch_id'] ?? null);
        $scope = ['is_all_branches' => $isAllBranches, 'branch_id' => $branchId];
        $setting = CashBankAccountSetting::query()
            ->where('company_id', $companyId)
            ->where('cash_bank_account_id', $account->id)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when(! $branchId, fn ($query) => $query->whereNull('branch_id'))
            ->first();
        $configuration = [
            ...$scope,
            'cash_bank_account_id' => $account->id,
            'can_receive_money' => $data['can_receive_money'],
            'can_send_money' => $data['can_send_money'],
            'is_active' => $data['is_active'] ?? true,
            'is_default_receive' => $this->shouldBeDefault($companyId, $account->id, $branchId, 'is_default_receive', $data),
            'is_default_payment' => $this->shouldBeDefault($companyId, $account->id, $branchId, 'is_default_payment', $data),
        ];

        $setting ? $this->settings->update($setting, $configuration) : $this->settings->create($companyId, $configuration);
    }

    private function shouldBeDefault(string $companyId, string $accountId, ?string $branchId, string $column, array $data): bool
    {
        $enabled = $column === 'is_default_receive' ? $data['can_receive_money'] : $data['can_send_money'];
        if (! $enabled || ! ($data['is_active'] ?? true)) return false;

        $hasDefault = CashBankAccountSetting::query()
            ->where('company_id', $companyId)
            ->where($column, true)
            ->where('cash_bank_account_id', '!=', $accountId)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when(! $branchId, fn ($query) => $query->whereNull('branch_id'))
            ->exists();

        return ! $hasDefault || $data['replace_default'];
    }

    private function accountData(array $data): array
    {
        return collect($data)->only(['name', 'type', 'bank_name', 'account_number', 'account_holder', 'opening_balance'])->merge([
            'currency' => $data['currency'] ?? 'IDR',
            'is_active' => $data['is_active'] ?? true,
        ])->all();
    }
}
