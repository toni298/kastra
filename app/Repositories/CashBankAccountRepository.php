<?php

namespace App\Repositories;

use App\Models\CashBankAccount;
use Illuminate\Contracts\Pagination\CursorPaginator;

class CashBankAccountRepository
{
    public function cursorPaginate(string $companyId, ?string $cursor = null): CursorPaginator
    {
        return CashBankAccount::query()
            ->with('settings.branch:id,name')
            ->where('company_id', $companyId)
            ->orderByDesc('is_active')->orderBy('name')->orderBy('id')
            ->cursorPaginate(20, ['id', 'name', 'type', 'bank_name', 'account_number', 'account_holder', 'currency', 'opening_balance', 'current_balance', 'is_active'], 'cursor', $cursor);
    }
}
