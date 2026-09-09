<?php

namespace App\Repositories;

use App\Models\CashBankAccountSetting;
use Illuminate\Contracts\Pagination\CursorPaginator;

class CashBankAccountSettingRepository
{
    public function cursorPaginate(string $companyId, array $filters): CursorPaginator
    {
        return CashBankAccountSetting::query()
            ->with(['account:id,name,type,bank_name,account_number', 'branch:id,name'])
            ->where('company_id', $companyId)
            ->when($filters['branch_id'] ?? null, fn ($query, $branchId) => $query->where('branch_id', $branchId))
            ->when(array_key_exists('branch_id', $filters) && $filters['branch_id'] === null, fn ($query) => $query->whereNull('branch_id'))
            ->orderByDesc('branch_id')
            ->orderByDesc('is_active')
            ->latest()
            ->cursorPaginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }

    public function resolveForBranch(string $companyId, string $accountId, ?string $branchId): ?CashBankAccountSetting
    {
        return CashBankAccountSetting::query()
            ->where('company_id', $companyId)
            ->where('cash_bank_account_id', $accountId)
            ->where('is_active', true)
            ->when($branchId, fn ($query) => $query->where(fn ($scope) => $scope->where('branch_id', $branchId)->orWhereNull('branch_id')))
            ->when(! $branchId, fn ($query) => $query->whereNull('branch_id'))
            ->orderByDesc('branch_id')
            ->first();
    }

    public function defaultForBranch(string $companyId, ?string $branchId, string $type): ?CashBankAccountSetting
    {
        $defaultColumn = $type === 'receive' ? 'is_default_receive' : 'is_default_payment';

        return CashBankAccountSetting::query()
            ->with('account')
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where($defaultColumn, true)
            ->when($branchId, fn ($query) => $query->where(fn ($scope) => $scope->where('branch_id', $branchId)->orWhereNull('branch_id')))
            ->when(! $branchId, fn ($query) => $query->whereNull('branch_id'))
            ->orderByDesc('branch_id')
            ->first();
    }
}
